<?php

namespace App\Jobs;

use App\Models\MediaFile;
use App\Services\LlmClient;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Collection;

class SegmentTranscriptIntoChapters implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(public MediaFile $mediaFile)
    {
        $this->onQueue('chapters');
    }

    public function handle(LlmClient $llm): void
    {
        $this->mediaFile->update(['chapter_generation_status' => 'processing']);

        try {
            $proposed = $llm->proposeChapters($this->mediaFile->fresh()->transcript ?? [], (int) $this->mediaFile->duration);

            $this->mediaFile->update([
                'chapter_proposal' => $this->sanitize($proposed, (int) $this->mediaFile->duration),
                'chapter_generation_status' => 'completed',
                'chapter_generation_error' => null,
            ]);
        } catch (\Throwable $e) {
            $this->mediaFile->update([
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
