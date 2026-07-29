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

it('forbids shared media references from generating chapters', function () {
    Queue::fake();
    $owner = User::factory()->create();
    $sharedUser = User::factory()->create();
    $mediaFile = MediaFile::factory()->create([
        'user_id' => $owner->id,
        'duration' => 600,
        'transcript' => [['start' => 0, 'end' => 600, 'text' => 'Existing transcript']],
        'chapter_generation_status' => 'completed',
    ]);
    $ownerItem = LibraryItem::factory()->create(['user_id' => $owner->id, 'media_file_id' => $mediaFile->id]);
    $sharedItem = LibraryItem::factory()->create(['user_id' => $sharedUser->id, 'media_file_id' => $mediaFile->id]);

    $this->actingAs($sharedUser)->post("/library/{$sharedItem->id}/chapters/generate")->assertForbidden();

    expect($mediaFile->fresh()->only(['transcript', 'chapter_generation_status']))->toBe([
        'transcript' => [['start' => 0, 'end' => 600, 'text' => 'Existing transcript']],
        'chapter_generation_status' => 'completed',
    ]);
    Queue::assertNothingPushed();

    $this->actingAs($owner)->post("/library/{$ownerItem->id}/chapters/generate")->assertRedirect();

    expect($mediaFile->fresh()->chapter_generation_status)->toBe('pending');
    Queue::assertPushed(TranscribeMediaFile::class);
});
