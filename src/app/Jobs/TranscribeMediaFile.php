<?php

namespace App\Jobs;

use App\Models\MediaFile;
use App\Services\Transcription\WhisperClient;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;

class TranscribeMediaFile implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(public MediaFile $mediaFile, public int $generationVersion = 0)
    {
        $this->onConnection('chapters');
        $this->onQueue('chapters');
    }

    public function handle(WhisperClient $whisper): void
    {
        $mediaFile = $this->mediaFile->fresh();

        if (! $this->isCurrent()) {
            return;
        }

        $duration = (int) ($mediaFile->duration ?? 0);
        $existing = $mediaFile->transcript ?? [];
        $resumeAt = collect($existing)->max('end') ?: 0;

        // Already fully transcribed (re-proposal) — nothing to do.
        if (! empty($existing) && $duration > 0 && $resumeAt >= $duration) {
            return;
        }

        if (! $this->updateCurrent(['chapter_generation_status' => 'processing'])) {
            return;
        }

        $chunkSeconds = (int) config('services.whisper.chunk_seconds', 1800);
        $source = Storage::disk('public')->path($mediaFile->file_path);
        $chunking = $whisper->chunk($source, $chunkSeconds);

        try {
            $segments = $existing;

            foreach ($chunking['segments'] as $chunk) {
                // Resume: skip chunks already covered by the saved transcript.
                if ($chunk['offset'] < $resumeAt) {
                    continue;
                }

                foreach ($whisper->transcribeFile($chunk['path']) as $seg) {
                    $segments[] = [
                        'start' => $seg['start'] + $chunk['offset'],
                        'end' => $seg['end'] + $chunk['offset'],
                        'text' => $seg['text'],
                    ];
                }

                // Checkpoint after each chunk so a crash/timeout resumes here, not from zero.
                if (! $this->updateCurrent(['transcript' => $segments])) {
                    return;
                }
            }
        } catch (\Throwable $e) {
            // Partial transcript is already saved; surface the failure and stop the chain.
            $this->updateCurrent([
                'chapter_generation_status' => 'failed',
                'chapter_generation_error' => $e->getMessage(),
            ]);

            throw $e;
        } finally {
            $whisper->cleanupChunks($chunking['dir']);
        }
    }

    public function failed(?\Throwable $exception): void
    {
        $this->updateCurrent([
            'transcript' => null,
            'chapter_generation_status' => 'failed',
            'chapter_generation_error' => $exception?->getMessage(),
        ]);
    }

    private function isCurrent(): bool
    {
        return MediaFile::query()->whereKey($this->mediaFile->id)
            ->where('chapter_generation_version', $this->generationVersion)->exists();
    }

    /** @param array<string, mixed> $attributes */
    private function updateCurrent(array $attributes): bool
    {
        return MediaFile::query()->whereKey($this->mediaFile->id)
            ->where('chapter_generation_version', $this->generationVersion)->update($attributes) === 1;
    }
}
