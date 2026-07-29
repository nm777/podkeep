<?php

use App\Enums\ProcessingStatusType;
use App\Models\LibraryItem;
use App\Models\MediaFile;
use App\Models\User;
use Illuminate\Support\Facades\Storage;

it('can delete a library item', function () {
    Storage::fake('public');

    $user = User::factory()->create();
    $mediaFile = MediaFile::factory()->create();

    $libraryItem = LibraryItem::factory()->create([
        'user_id' => $user->id,
        'media_file_id' => $mediaFile->id,
    ]);

    $response = $this->actingAs($user)->delete("/library/{$libraryItem->id}");

    $response->assertRedirect('/library');
    $response->assertSessionHas('success');

    $this->assertDatabaseMissing('library_items', [
        'id' => $libraryItem->id,
    ]);
});

it('deletes the staged upload when deleting a pending library item', function () {
    Storage::fake('public');

    $user = User::factory()->create();
    $tempPath = 'temp-uploads/pending-web-upload.mp3';
    Storage::disk('public')->put($tempPath, 'fake content');
    $libraryItem = LibraryItem::factory()->create([
        'user_id' => $user->id,
        'media_file_id' => null,
        'processing_status' => ProcessingStatusType::PENDING,
        'temp_file_path' => $tempPath,
    ]);

    $this->actingAs($user)->delete("/library/{$libraryItem->id}")
        ->assertRedirect('/library');

    Storage::disk('public')->assertMissing($tempPath);
});

it('does not delete storage when deleting an item without a staged upload', function () {
    Storage::fake('public');

    $user = User::factory()->create();
    $unrelatedPath = 'temp-uploads/unrelated.mp3';
    Storage::disk('public')->put($unrelatedPath, 'fake content');
    $libraryItem = LibraryItem::factory()->create([
        'user_id' => $user->id,
        'media_file_id' => null,
        'temp_file_path' => null,
    ]);

    $this->actingAs($user)->delete("/library/{$libraryItem->id}")
        ->assertRedirect('/library');

    Storage::disk('public')->assertExists($unrelatedPath);
});

it('cannot delete another user library item', function () {
    $user = User::factory()->create();
    $otherUser = User::factory()->create();
    $mediaFile = MediaFile::factory()->create();

    $libraryItem = LibraryItem::factory()->create([
        'user_id' => $otherUser->id,
        'media_file_id' => $mediaFile->id,
    ]);

    $response = $this->actingAs($user)->delete("/library/{$libraryItem->id}");

    $response->assertForbidden();
});

it('deletes orphaned media files when last library item is removed', function () {
    Storage::fake('public');

    $user = User::factory()->create();
    $mediaFile = MediaFile::factory()->create([
        'file_path' => 'media/test-file.mp3',
    ]);

    Storage::disk('public')->put($mediaFile->file_path, 'fake content');

    $libraryItem = LibraryItem::factory()->create([
        'user_id' => $user->id,
        'media_file_id' => $mediaFile->id,
    ]);

    $this->actingAs($user)->delete("/library/{$libraryItem->id}");

    $this->assertDatabaseMissing('media_files', [
        'id' => $mediaFile->id,
    ]);

    Storage::disk('public')->assertMissing($mediaFile->file_path);
});

it('keeps media files when other users still reference them', function () {
    Storage::fake('public');

    $user = User::factory()->create();
    $otherUser = User::factory()->create();
    $mediaFile = MediaFile::factory()->create([
        'file_path' => 'media/shared-file.mp3',
    ]);

    Storage::disk('public')->put($mediaFile->file_path, 'fake content');

    $userItem = LibraryItem::factory()->create([
        'user_id' => $user->id,
        'media_file_id' => $mediaFile->id,
    ]);

    $otherUserItem = LibraryItem::factory()->create([
        'user_id' => $otherUser->id,
        'media_file_id' => $mediaFile->id,
    ]);

    $this->actingAs($user)->delete("/library/{$userItem->id}");

    $this->assertDatabaseHas('media_files', [
        'id' => $mediaFile->id,
    ]);

    Storage::disk('public')->assertExists($mediaFile->file_path);
});
