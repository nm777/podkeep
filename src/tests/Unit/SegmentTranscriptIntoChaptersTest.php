<?php

use App\Jobs\SegmentTranscriptIntoChapters;
use App\Models\MediaFile;

test('caps automatically generated chapters at 20', function () {
    $job = new class(new MediaFile) extends SegmentTranscriptIntoChapters
    {
        /**
         * @param  array<int, array{start: int, title: string}>  $chapters
         * @return array<int, array{start_time: int, title: string}>
         */
        public function sanitizeChapters(array $chapters): array
        {
            return $this->sanitize($chapters, 1000);
        }
    };

    $chapters = collect(range(0, 20))
        ->map(fn (int $start) => ['start' => $start, 'title' => "Chapter {$start}"])
        ->all();

    expect($job->sanitizeChapters($chapters))->toHaveCount(20);
});
