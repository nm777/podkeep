<?php

use App\Jobs\ProcessMediaFile;
use App\Jobs\ProcessYouTubeAudio;
use App\Models\LibraryItem;

it('aligns queue worker timeouts with the database reservation window', function () {
    $libraryItem = LibraryItem::factory()->make();
    $mediaTimeout = (new ProcessMediaFile($libraryItem))->timeout;
    $youtubeTimeout = (new ProcessYouTubeAudio($libraryItem, 'https://www.youtube.com/watch?v=dQw4w9WgXcQ'))->timeout;
    $chapterWorkerTimeout = 43200;
    $retryAfter = config('queue.connections.database.retry_after');

    expect($mediaTimeout)->toBe(720)
        ->and($youtubeTimeout)->toBe(360)
        ->and($chapterWorkerTimeout)->toBe(43200)
        ->and($retryAfter)->toBe(43260)
        ->and($retryAfter)->toBeGreaterThan($mediaTimeout)
        ->and($retryAfter)->toBeGreaterThan($youtubeTimeout)
        ->and($retryAfter)->toBeGreaterThan($chapterWorkerTimeout);
});
