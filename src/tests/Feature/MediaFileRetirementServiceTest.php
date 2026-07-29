<?php

use App\Models\LibraryItem;
use App\Models\MediaFile;
use App\Models\User;
use App\Services\MediaFileRetirementService;
use Illuminate\Support\Facades\Storage;

it('retires an orphaned media file after deleting its database record', function () {
    Storage::fake('public');
    $mediaFile = MediaFile::factory()->create(['file_path' => 'media/orphan.mp3']);
    Storage::disk('public')->put($mediaFile->file_path, 'audio');

    expect(MediaFileRetirementService::retire($mediaFile))->toBeTrue();

    $this->assertDatabaseMissing('media_files', ['id' => $mediaFile->id]);
    Storage::disk('public')->assertMissing($mediaFile->file_path);
});

it('keeps a media file linked before retirement acquires its lock', function () {
    Storage::fake('public');
    $mediaFile = MediaFile::factory()->create(['file_path' => 'media/linked.mp3']);
    Storage::disk('public')->put($mediaFile->file_path, 'audio');

    $libraryItem = LibraryItem::factory()->create([
        'user_id' => User::factory(),
        'media_file_id' => $mediaFile->id,
    ]);

    expect(MediaFileRetirementService::retire($mediaFile))->toBeFalse();

    $this->assertDatabaseHas('media_files', ['id' => $mediaFile->id]);
    expect($libraryItem->refresh()->media_file_id)->toBe($mediaFile->id);
    Storage::disk('public')->assertExists($mediaFile->file_path);
});

it('keeps storage used by another media file', function () {
    Storage::fake('public');
    $orphan = MediaFile::factory()->create(['file_path' => 'media/shared.mp3']);
    $referenced = MediaFile::factory()->create(['file_path' => 'media/shared.mp3']);
    Storage::disk('public')->put($orphan->file_path, 'audio');
    LibraryItem::factory()->create(['media_file_id' => $referenced->id]);

    expect(MediaFileRetirementService::retire($orphan))->toBeTrue();

    $this->assertDatabaseMissing('media_files', ['id' => $orphan->id]);
    Storage::disk('public')->assertExists($orphan->file_path);
});
