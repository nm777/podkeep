<?php

namespace App\Jobs;

use App\Models\Chapter;
use App\Models\MediaFile;
use App\Services\LlmClient;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class SegmentTranscriptIntoChapters implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /** @var list<int> */
    public array $backoff = [60, 300];

    public function __construct(public MediaFile $mediaFile, public int $generationVersion = 0)
    {
        $this->onConnection('chapters');
        $this->onQueue('chapters');
    }

    public function handle(LlmClient $llm): void
    {
        $mediaFile = $this->mediaFile->fresh();

        if (! $this->isCurrent()) {
            return;
        }

        $transcript = $mediaFile->transcript ?? [];
        $transcriptHash = md5(json_encode($transcript));

        // Idempotent: chapters already exist for this transcript, including after it was cleared on success.
        if ($mediaFile->chapters()->exists() && (
            $mediaFile->chapter_proposal_for_hash === $transcriptHash ||
            ($mediaFile->chapter_generation_status === 'completed' && $mediaFile->transcript === null && $mediaFile->chapter_proposal_for_hash !== null)
        )) {
            $this->updateCurrent(['chapter_generation_status' => 'completed']);

            return;
        }

        if (! $this->updateCurrent(['chapter_generation_status' => 'processing'])) {
            return;
        }

        try {
            /** @var array<int, array<int, array{start: mixed, title: mixed}>> $checkpoints */
            $checkpoints = $mediaFile->chapter_proposal ?? [];

            $proposed = $llm->proposeChapters(
                $transcript,
                (int) $mediaFile->duration,
                $checkpoints,
                function (array $checkpoints): void {
                    $this->updateCurrent(['chapter_proposal' => $checkpoints]);
                },
            );
            $chapters = $this->sanitize($proposed, (int) $mediaFile->duration);

            $generated = DB::transaction(function () use ($mediaFile, $chapters, $transcriptHash) {
                $mediaFile = MediaFile::query()->lockForUpdate()->findOrFail($mediaFile->id);

                if ($mediaFile->chapter_generation_version !== $this->generationVersion) {
                    return false;
                }

                $mediaFile->chapters()->delete();

                foreach ($chapters as $chapter) {
                    $mediaFile->chapters()->create([
                        'start_time' => $chapter['start_time'],
                        'title' => $chapter['title'],
                    ]);
                }

                $mediaFile->update([
                    'transcript' => null,
                    'chapter_proposal' => null,
                    'chapter_proposal_for_hash' => $transcriptHash,
                    'chapter_generation_status' => 'completed',
                    'chapter_generation_error' => null,
                ]);

                return true;
            });

            if (! $generated) {
                return;
            }

            // Invalidate RSS cache for affected feeds.
            foreach ($mediaFile->libraryItems()->with('feedItems')->get() as $libItem) {
                foreach ($libItem->feedItems as $feedItem) {
                    Cache::forget("rss.{$feedItem->feed_id}");
                }
            }
        } catch (\Throwable $e) {
            $this->updateCurrent([
                'chapter_generation_status' => 'failed',
                'chapter_generation_error' => $e->getMessage(),
            ]);

            throw $e;
        }
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

    /**
     * Sanitize LLM output into valid chapter drafts: cap 20, clamp within duration, unique, sorted, non-empty titles.
     *
     * @param  array<int, array<string, mixed>>  $proposed
     * @return array<int, array{start_time: int, title: string}>
     */
    protected function sanitize(array $proposed, int $duration): array
    {
        return collect($proposed)
            ->filter(fn ($c) => trim((string) ($c['title'] ?? '')) !== '')
            ->map(fn ($c) => ['start_time' => (int) ($c['start'] ?? 0), 'title' => mb_substr((string) $c['title'], 0, 255)])
            ->filter(fn ($c) => $c['start_time'] >= 0 && ($duration <= 0 || $c['start_time'] < $duration))
            ->pipe(fn (Collection $items) => $items->unique('start_time'))
            ->sortBy('start_time')
            ->take(20)
            ->values()
            ->all();
    }
}
