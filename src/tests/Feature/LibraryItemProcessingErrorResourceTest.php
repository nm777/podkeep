<?php

use App\Http\Resources\LibraryItemResource;
use App\Models\LibraryItem;
use App\Models\User;

test('does not expose stored processing exception text', function () {
    $sensitiveError = 'Download failed: https://user:secret@example.com/audio.mp3?token=secret';
    $libraryItem = LibraryItem::factory()->create([
        'user_id' => User::factory(),
        'processing_error' => $sensitiveError,
    ]);

    $data = LibraryItemResource::make($libraryItem)->resolve();

    $this->assertSame('Media processing failed.', $data['processing_error']);
    $this->assertStringNotContainsString($sensitiveError, (string) $data['processing_error']);
});
