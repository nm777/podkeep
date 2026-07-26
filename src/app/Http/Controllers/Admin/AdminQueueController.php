<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
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

        return Inertia::render('admin/queue/index', [
            'pending' => $pending,
            'executing' => $executing,
            'failed' => $failed,
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
}
