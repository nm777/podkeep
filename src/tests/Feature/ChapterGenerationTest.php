<?php

use App\Jobs\SegmentTranscriptIntoChapters;
use App\Jobs\TranscribeMediaFile;
use App\Models\LibraryItem;
use App\Models\MediaFile;
use App\Models\User;
use Illuminate\Support\Facades\Queue;

it('sets status pending and dispatches the chain on the chapters queue', function () {
    Queue::fake();
    $user = User::factory()->create();
    $mediaFile = MediaFile::factory()->create(['user_id' => $user->id, 'duration' => 600]);
    $libraryItem = LibraryItem::factory()->create(['user_id' => $user->id, 'media_file_id' => $mediaFile->id]);

    $response = $this->actingAs($user)->post("/library/{$libraryItem->id}/chapters/generate");

    $response->assertRedirect();
    expect($mediaFile->fresh()->chapter_generation_status)->toBe('pending');
    Queue::assertPushedWithChain(
        TranscribeMediaFile::class,
        [SegmentTranscriptIntoChapters::class],
        fn ($job) => $job->queue === 'chapters',
    );
});

it('requires a processed media file with a duration', function () {
    Queue::fake();
    $user = User::factory()->create();
    $mediaFile = MediaFile::factory()->create(['user_id' => $user->id, 'duration' => null]);
    $libraryItem = LibraryItem::factory()->create(['user_id' => $user->id, 'media_file_id' => $mediaFile->id]);

    $response = $this->actingAs($user)->post("/library/{$libraryItem->id}/chapters/generate");

    $response->assertSessionHas('warning');
    Queue::assertNothingPushed();
});

it('forbids non-owners from generating chapters', function () {
    $owner = User::factory()->create();
    $other = User::factory()->create();
    $mediaFile = MediaFile::factory()->create(['user_id' => $owner->id, 'duration' => 600]);
    $libraryItem = LibraryItem::factory()->create(['user_id' => $owner->id, 'media_file_id' => $mediaFile->id]);

    $response = $this->actingAs($other)->post("/library/{$libraryItem->id}/chapters/generate");

    $response->assertForbidden();
});
