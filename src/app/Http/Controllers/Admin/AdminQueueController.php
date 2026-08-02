<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\MediaFile;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Collection;
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
            ->get();

        // Executing: reserved_at IS NOT NULL
        $executing = DB::table('jobs')
            ->whereNotNull('reserved_at')
            ->orderBy('reserved_at', 'desc')
            ->limit(50)
            ->get();

        // Failed jobs (paginated)
        $failed = DB::table('failed_jobs')
            ->orderBy('failed_at', 'desc')
            ->limit(20)
            ->get();

        $mediaFiles = $this->mediaFilesFor($pending->concat($executing)->concat($failed));

        $pending = $pending->map(fn ($job) => [
                'id' => $job->id,
                'type' => $this->parseJobType($job->payload),
                'media' => $this->mediaFileDetails($job->payload, $mediaFiles),
                'queue' => $job->queue,
                'attempts' => $job->attempts,
                'created_at' => $job->created_at,
            ]);

        $executing = $executing->map(fn ($job) => [
                'id' => $job->id,
                'type' => $this->parseJobType($job->payload),
                'media' => $this->mediaFileDetails($job->payload, $mediaFiles),
                'queue' => $job->queue,
                'attempts' => $job->attempts,
                'reserved_at' => $job->reserved_at,
                'created_at' => $job->created_at,
            ]);

        $failed = $failed->map(fn ($job) => [
                'id' => $job->id,
                'uuid' => $job->uuid,
                'type' => $this->parseJobType($job->payload),
                'media' => $this->mediaFileDetails($job->payload, $mediaFiles),
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

    /**
     * @param  Collection<int, \stdClass>  $jobs
     * @return Collection<int, MediaFile>
     */
    private function mediaFilesFor(Collection $jobs): Collection
    {
        $mediaFileIds = $jobs->map(fn (object $job) => $this->parseMediaFileId($job->payload))
            ->filter()
            ->unique();

        return MediaFile::query()
            ->select(['id', 'file_path'])
            ->with('libraryItems:id,media_file_id,title')
            ->whereKey($mediaFileIds)
            ->get()
            ->keyBy('id');
    }

    /**
     * @param  Collection<int, MediaFile>  $mediaFiles
     * @return array{id: int, title: string|null, url: string}|null
     */
    private function mediaFileDetails(string $payload, Collection $mediaFiles): ?array
    {
        $mediaFile = $mediaFiles->get($this->parseMediaFileId($payload));

        if (! $mediaFile instanceof MediaFile) {
            return null;
        }

        return [
            'id' => $mediaFile->id,
            'title' => $mediaFile->libraryItems->first()?->title,
            'url' => route('files.show', ['file_path' => $mediaFile->file_path]),
        ];
    }

    private function parseMediaFileId(string $payload): ?int
    {
        $decoded = json_decode($payload, true);
        $command = $decoded['data']['command'] ?? null;

        if (! is_string($command) || ! preg_match('/s:5:"class";s:\d+:"App\\\\Models\\\\MediaFile";s:2:"id";i:(\d+);/', $command, $matches)) {
            return null;
        }

        return (int) $matches[1];
    }

    public function cancel(int $id): RedirectResponse
    {
        DB::table('jobs')->where('id', $id)->whereNull('reserved_at')->delete();

        return back()->with('success', 'Job cancelled.');
    }

    public function release(int $id): RedirectResponse
    {
        return back()->with('warning', 'Executing jobs cannot be released.');
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
