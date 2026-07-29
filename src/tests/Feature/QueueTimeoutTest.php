<?php

use App\Jobs\ProcessMediaFile;
use App\Jobs\ProcessYouTubeAudio;
use App\Models\LibraryItem;

it('uses separate database reservation windows for ordinary and chapter jobs', function () {
    $libraryItem = LibraryItem::factory()->make();
    $mediaTimeout = (new ProcessMediaFile($libraryItem))->timeout;
    $youtubeTimeout = (new ProcessYouTubeAudio($libraryItem, 'https://www.youtube.com/watch?v=dQw4w9WgXcQ'))->timeout;
    $chapterWorkerTimeout = 43200;
    $defaultRetryAfter = config('queue.connections.database.retry_after');
    $chapterRetryAfter = config('queue.connections.chapters.retry_after');

    expect($mediaTimeout)->toBe(720)
        ->and($youtubeTimeout)->toBe(360)
        ->and($chapterWorkerTimeout)->toBe(43200)
        ->and($defaultRetryAfter)->toBe(780)
        ->and($chapterRetryAfter)->toBe(43260)
        ->and($defaultRetryAfter)->toBeGreaterThan($mediaTimeout)
        ->and($defaultRetryAfter)->toBeGreaterThan($youtubeTimeout)
        ->and($chapterRetryAfter)->toBeGreaterThan($chapterWorkerTimeout);
});
