<?php

use App\Models\LibraryItem;
use App\Models\MediaFile;
use App\Models\User;
use App\Services\DuplicateDetectionService;
use Illuminate\Support\Facades\Storage;

beforeEach(function () {
    Storage::disk('media')->deleteDirectory('media');
    Storage::disk('media')->makeDirectory('media');
});

afterEach(function () {
    Storage::disk('media')->deleteDirectory('media');
});

it('findGlobalDuplicate returns matching MediaFile via DuplicateDetectionService', function () {
    $user = User::factory()->create();
    $tempPath = 'media/find-dup-test.mp3';
    $content = fake()->regexify('[a-z]{500}');
    Storage::disk('media')->put($tempPath, $content);

    $mediaFile = MediaFile::factory()->create([
        'user_id' => $user->id,
        'file_hash' => hash_file('sha256', Storage::disk('media')->path($tempPath)),
    ]);

    LibraryItem::factory()->create([
        'user_id' => $user->id,
        'media_file_id' => $mediaFile->id,
    ]);

    $result = DuplicateDetectionService::findGlobalDuplicate($tempPath);
    expect($result)->not->toBeNull();
    expect($result->id)->toBe($mediaFile->id);
});

it('findGlobalDuplicate returns null for non-existent file', function () {
    $result = DuplicateDetectionService::findGlobalDuplicate('/non/existent/file.mp3');
    expect($result)->toBeNull();
});

it('findUserDuplicate returns matching MediaFile for correct user via DuplicateDetectionService', function () {
    $user = User::factory()->create();
    $otherUser = User::factory()->create();
    $tempPath = 'media/find-user-dup-test.mp3';
    $content = fake()->regexify('[a-z]{500}');
    Storage::disk('media')->put($tempPath, $content);

    $mediaFile = MediaFile::factory()->create([
        'user_id' => $user->id,
        'file_hash' => hash_file('sha256', Storage::disk('media')->path($tempPath)),
    ]);

    LibraryItem::factory()->create([
        'user_id' => $user->id,
        'media_file_id' => $mediaFile->id,
    ]);

    $result = DuplicateDetectionService::findUserDuplicate($tempPath, $user->id);
    expect($result)->not->toBeNull();
    expect($result->id)->toBe($mediaFile->id);

    $otherResult = DuplicateDetectionService::findUserDuplicate($tempPath, $otherUser->id);
    expect($otherResult)->toBeNull();
});
