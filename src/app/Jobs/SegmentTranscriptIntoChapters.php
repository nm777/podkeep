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
use Illuminate\Support\Facades\DB;

class SegmentTranscriptIntoChapters implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(public MediaFile $mediaFile)
    {
        $this->onConnection('chapters');
        $this->onQueue('chapters');
    }

    public function handle(LlmClient $llm): void
    {
        $mediaFile = $this->mediaFile->fresh();
        $transcript = $mediaFile->transcript ?? [];
        $transcriptHash = md5(json_encode($transcript));

        // Idempotent: chapters already exist for this exact transcript, so skip.
        if ($mediaFile->chapter_proposal_for_hash === $transcriptHash && $mediaFile->chapters()->exists()) {
            $mediaFile->update(['chapter_generation_status' => 'completed']);

            return;
        }

        $mediaFile->update(['chapter_generation_status' => 'processing']);

        try {
            $proposed = $llm->proposeChapters($transcript, (int) $mediaFile->duration);
            $chapters = $this->sanitize($proposed, (int) $mediaFile->duration);

            DB::transaction(function () use ($mediaFile, $chapters, $transcriptHash) {
                $mediaFile->chapters()->delete();

                foreach ($chapters as $chapter) {
                    $mediaFile->chapters()->create([
                        'start_time' => $chapter['start_time'],
                        'title' => $chapter['title'],
                    ]);
                }

                $mediaFile->update([
                    'chapter_proposal_for_hash' => $transcriptHash,
                    'chapter_generation_status' => 'completed',
                    'chapter_generation_error' => null,
                ]);
            });

            // Invalidate RSS cache for affected feeds.
            foreach ($mediaFile->libraryItems()->with('feedItems')->get() as $libItem) {
                foreach ($libItem->feedItems as $feedItem) {
                    \Illuminate\Support\Facades\Cache::forget("rss.{$feedItem->feed_id}");
                }
            }
        } catch (\Throwable $e) {
            $mediaFile->update([
                'chapter_generation_status' => 'failed',
                'chapter_generation_error' => $e->getMessage(),
            ]);
        }
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
