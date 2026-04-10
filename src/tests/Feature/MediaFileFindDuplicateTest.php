<?php

use App\Models\MediaFile;
use App\Models\User;
use Illuminate\Support\Facades\Storage;

beforeEach(function () {
    Storage::disk('public')->deleteDirectory('media');
    Storage::disk('public')->makeDirectory('media');
});

afterEach(function () {
    Storage::disk('public')->deleteDirectory('media');
});

it('findDuplicateByFile returns matching MediaFile', function () {
    $user = User::factory()->create();
    $tempPath = 'media/find-dup-test.mp3';
    $content = fake()->regexify('[a-z]{500}');
    Storage::disk('public')->put($tempPath, $content);

    $mediaFile = MediaFile::factory()->create([
        'user_id' => $user->id,
        'file_hash' => hash_file('sha256', Storage::disk('public')->path($tempPath)),
    ]);

    $result = MediaFile::findDuplicateByFile($tempPath);
    expect($result)->not->toBeNull();
    expect($result->id)->toBe($mediaFile->id);
});

it('findDuplicateByFile returns null for non-existent file', function () {
    $result = MediaFile::findDuplicateByFile('/non/existent/file.mp3');
    expect($result)->toBeNull();
});

it('findDuplicateByFileForUser returns matching MediaFile for correct user', function () {
    $user = User::factory()->create();
    $otherUser = User::factory()->create();
    $tempPath = 'media/find-user-dup-test.mp3';
    $content = fake()->regexify('[a-z]{500}');
    Storage::disk('public')->put($tempPath, $content);

    $mediaFile = MediaFile::factory()->create([
        'user_id' => $user->id,
        'file_hash' => hash_file('sha256', Storage::disk('public')->path($tempPath)),
    ]);

    \App\Models\LibraryItem::factory()->create([
        'user_id' => $user->id,
        'media_file_id' => $mediaFile->id,
    ]);

    $result = MediaFile::findDuplicateByFileForUser($tempPath, $user->id);
    expect($result)->not->toBeNull();
    expect($result->id)->toBe($mediaFile->id);

    $otherResult = MediaFile::findDuplicateByFileForUser($tempPath, $otherUser->id);
    expect($otherResult)->toBeNull();
});
