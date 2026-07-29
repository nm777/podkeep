<?php

use App\Jobs\ProcessMediaFile;
use App\Jobs\ProcessYouTubeAudio;
use App\Models\LibraryItem;

it('aligns default queue job timeouts with the database reservation window', function () {
    $libraryItem = LibraryItem::factory()->make();
    $mediaTimeout = (new ProcessMediaFile($libraryItem))->timeout;
    $youtubeTimeout = (new ProcessYouTubeAudio($libraryItem, 'https://www.youtube.com/watch?v=dQw4w9WgXcQ'))->timeout;
    $retryAfter = config('queue.connections.database.retry_after');

    expect($mediaTimeout)->toBe(720)
        ->and($youtubeTimeout)->toBe(360)
        ->and($retryAfter)->toBe(750)
        ->and($retryAfter)->toBeGreaterThan($mediaTimeout)
        ->and($retryAfter)->toBeGreaterThan($youtubeTimeout);
});
