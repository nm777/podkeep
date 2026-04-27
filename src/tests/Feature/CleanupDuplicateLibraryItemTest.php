<?php

use App\Jobs\CleanupDuplicateLibraryItem;
use App\Models\LibraryItem;
use App\Models\MediaFile;
use App\Models\User;
use App\ProcessingStatusType;

it('deletes a duplicate library item when a valid original exists', function () {
    $user = User::factory()->create();
    $mediaFile = MediaFile::factory()->create(['user_id' => $user->id]);

    $originalItem = LibraryItem::factory()->create([
        'user_id' => $user->id,
        'media_file_id' => $mediaFile->id,
        'is_duplicate' => false,
        'processing_status' => ProcessingStatusType::COMPLETED,
    ]);

    $duplicateItem = LibraryItem::factory()->create([
        'user_id' => $user->id,
        'media_file_id' => $mediaFile->id,
        'is_duplicate' => true,
        'processing_status' => ProcessingStatusType::COMPLETED,
    ]);

    $job = new CleanupDuplicateLibraryItem($duplicateItem);
    $job->handle();

    expect(LibraryItem::find($duplicateItem->id))->toBeNull();
    expect(LibraryItem::find($originalItem->id))->not->toBeNull();
});

it('adopts duplicate as primary when no valid original exists', function () {
    $user = User::factory()->create();
    $mediaFile = MediaFile::factory()->create(['user_id' => $user->id]);

    $item = LibraryItem::factory()->create([
        'user_id' => $user->id,
        'media_file_id' => $mediaFile->id,
        'is_duplicate' => true,
        'processing_status' => ProcessingStatusType::COMPLETED,
    ]);

    $job = new CleanupDuplicateLibraryItem($item);
    $job->handle();

    $item->refresh();
    expect($item)->not->toBeNull();
    expect($item->is_duplicate)->toBeFalse();
    expect($item->duplicate_detected_at)->toBeNull();
});

it('skips non-duplicate library items', function () {
    $user = User::factory()->create();
    $mediaFile = MediaFile::factory()->create(['user_id' => $user->id]);

    $item = LibraryItem::factory()->create([
        'user_id' => $user->id,
        'media_file_id' => $mediaFile->id,
        'is_duplicate' => false,
        'processing_status' => ProcessingStatusType::COMPLETED,
    ]);

    $job = new CleanupDuplicateLibraryItem($item);
    $job->handle();

    expect(LibraryItem::find($item->id))->not->toBeNull();
});

it('handles already-deleted items gracefully', function () {
    $user = User::factory()->create();
    $mediaFile = MediaFile::factory()->create(['user_id' => $user->id]);

    $item = LibraryItem::factory()->create([
        'user_id' => $user->id,
        'media_file_id' => $mediaFile->id,
        'is_duplicate' => true,
        'processing_status' => ProcessingStatusType::COMPLETED,
    ]);

    $item->delete();

    $job = new CleanupDuplicateLibraryItem($item);
    $job->handle();

    expect(LibraryItem::find($item->id))->toBeNull();
});
