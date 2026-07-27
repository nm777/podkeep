<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;

class AdminQueueController extends Controller
{
    public function index(): Response
    {
        // Pending: reserved_at IS NULL
        $pending = DB::table('jobs')
            ->whereNull('reserved_at')
            ->orderBy('id', 'desc')
            ->limit(50)
            ->get()
            ->map(fn ($job) => [
                'id' => $job->id,
                'type' => $this->parseJobType($job->payload),
                'queue' => $job->queue,
                'attempts' => $job->attempts,
                'created_at' => $job->created_at,
            ]);

        // Executing: reserved_at IS NOT NULL
        $executing = DB::table('jobs')
            ->whereNotNull('reserved_at')
            ->orderBy('reserved_at', 'desc')
            ->limit(50)
            ->get()
            ->map(fn ($job) => [
                'id' => $job->id,
                'type' => $this->parseJobType($job->payload),
                'queue' => $job->queue,
                'attempts' => $job->attempts,
                'reserved_at' => $job->reserved_at,
                'created_at' => $job->created_at,
            ]);

        // Failed jobs (paginated)
        $failed = DB::table('failed_jobs')
            ->orderBy('failed_at', 'desc')
            ->limit(20)
            ->get()
            ->map(fn ($job) => [
                'id' => $job->id,
                'uuid' => $job->uuid,
                'type' => $this->parseJobType($job->payload),
                'queue' => $job->queue,
                'failed_at' => $job->failed_at,
                'exception' => Str::limit($job->exception, 500),
            ]);

        $recentlyCompleted = DB::table('completed_job_log')
            ->orderBy('completed_at', 'desc')
            ->limit(20)
            ->get()
            ->map(fn ($job) => [
                'id' => $job->id,
                'job_type' => $job->job_type,
                'queue' => $job->queue,
                'completed_at' => $job->completed_at,
            ]);

        return Inertia::render('admin/queue/index', [
            'pending' => $pending,
            'executing' => $executing,
            'failed' => $failed,
            'recentlyCompleted' => $recentlyCompleted,
        ]);
    }

    /**
     * Parse the display name from a job's JSON payload.
     * Does NOT return the full payload (security).
     */
    private function parseJobType(string $payload): string
    {
        $decoded = json_decode($payload, true);

        return $decoded['displayName'] ?? 'Unknown';
    }

    public function cancel(int $id): RedirectResponse
    {
        DB::table('jobs')->where('id', $id)->whereNull('reserved_at')->delete();

        return back()->with('success', 'Job cancelled.');
    }

    public function release(int $id): RedirectResponse
    {
        DB::table('jobs')->where('id', $id)->whereNotNull('reserved_at')->update(['reserved_at' => null]);

        return back()->with('success', 'Job released for re-processing.');
    }

    public function retry(string $uuid): RedirectResponse
    {
        $failed = app('queue.failer')->find($uuid);

        if ($failed) {
            DB::table('jobs')->insert([
                'queue' => $failed->queue,
                'payload' => $failed->payload,
                'attempts' => 0,
                'reserved_at' => null,
                'available_at' => now()->timestamp,
                'created_at' => now()->timestamp,
            ]);
            app('queue.failer')->forget($uuid);
        }

        return back()->with('success', 'Job re-queued.');
    }

    public function delete(string $uuid): RedirectResponse
    {
        app('queue.failer')->forget($uuid);

        return back()->with('success', 'Failed job deleted.');
    }
}
