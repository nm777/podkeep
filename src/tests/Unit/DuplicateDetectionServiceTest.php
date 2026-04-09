<?php

use App\Models\LibraryItem;
use App\Models\MediaFile;
use App\Services\DuplicateDetectionService;
use App\Models\User;
use Illuminate\Support\Facades\Storage;

uses()->group('Unit', 'DuplicateDetectionService');

test('calculate file hash from existing file', function () {
    Storage::fake('public');
    
    $filePath = 'media/test-audio.mp3';
    $content = 'test audio content for hashing';
    Storage::disk('public')->put($filePath, $content);

    $expectedHash = hash('sha256', $content);
    $hash = DuplicateDetectionService::calculateFileHash($filePath);

    expect($hash)->toBe($expectedHash);
});

test('calculate file hash returns null for non-existent file', function () {
    Storage::fake('public');
    
    $hash = DuplicateDetectionService::calculateFileHash('media/non-existent.mp3');

    expect($hash)->toBeNull();
});

test('find global duplicate by file hash', function () {
    Storage::fake('public');
    
    $filePath = 'media/test-audio.mp3';
    Storage::disk('public')->put($filePath, 'test content');

    $mediaFile = MediaFile::factory()->create([
        'file_path' => 'media/different-file.mp3',
        'file_hash' => hash('sha256', 'test content'),
    ]);

    $duplicate = DuplicateDetectionService::findGlobalDuplicate($filePath);

    expect($duplicate)->toBe($mediaFile);
    expect($duplicate->id)->toBe($mediaFile->id);
});

test('find global duplicate returns null when no duplicate exists', function () {
    Storage::fake('public');
    
    $filePath = 'media/unique-audio.mp3';
    Storage::disk('public')->put($filePath, 'unique content');

    $duplicate = DuplicateDetectionService::findGlobalDuplicate($filePath);

    expect($duplicate)->toBeNull();
});

test('find user duplicate by file hash', function () {
    Storage::fake('public');
    
    $user = User::factory()->create();
    $filePath = 'media/test-audio.mp3';
    Storage::disk('public')->put($filePath, 'test content');

    $mediaFile = MediaFile::factory()->create([
        'user_id' => $user->id,
        'file_path' => 'media/different-file.mp3',
        'file_hash' => hash('sha256', 'test content'),
    ]);

    $libraryItem = LibraryItem::factory()->create([
        'user_id' => $user->id,
        'media_file_id' => $mediaFile->id,
    ]);

    $duplicate = DuplicateDetectionService::findUserDuplicate($filePath, $user->id);

    expect($duplicate)->toBe($mediaFile);
    expect($duplicate->id)->toBe($mediaFile->id);
});

test('find user duplicate returns null when file does not exist', function () {
    Storage::fake('public');
    
    $user = User::factory()->create();

    $duplicate = DuplicateDetectionService::findUserDuplicate('media/non-existent.mp3', $user->id);

    expect($duplicate)->toBeNull();
});

test('find url duplicate for user', function () {
    $user = User::factory()->create();
    $sourceUrl = 'https://example.com/audio.mp3';

    $existingItem = LibraryItem::factory()->create([
        'user_id' => $user->id,
        'source_url' => $sourceUrl,
        'source_type' => 'url',
    ]);

    $duplicate = DuplicateDetectionService::findUrlDuplicateForUser($sourceUrl, $user->id);

    expect($duplicate)->toBe($existingItem);
    expect($duplicate->id)->toBe($existingItem->id);
});

test('find url duplicate for user returns null when no duplicate', function () {
    $user = User::factory()->create();

    $duplicate = DuplicateDetectionService::findUrlDuplicateForUser('https://example.com/new-audio.mp3', $user->id);

    expect($duplicate)->toBeNull();
});

test('find url duplicate for user returns null for empty url', function () {
    $user = User::factory()->create();

    $duplicate = DuplicateDetectionService::findUrlDuplicateForUser('', $user->id);

    expect($duplicate)->toBeNull();
});

test('find global url duplicate', function () {
    $user1 = User::factory()->create();
    $sourceUrl = 'https://example.com/audio.mp3';

    $mediaFile = MediaFile::factory()->create([
        'user_id' => $user1->id,
        'source_url' => $sourceUrl,
        'file_hash' => 'test-hash-123',
    ]);

    $duplicate = DuplicateDetectionService::findGlobalUrlDuplicate($sourceUrl);

    expect($duplicate)->toBe($mediaFile);
});

test('find global url duplicate returns null when no duplicate', function () {
    $duplicate = DuplicateDetectionService::findGlobalUrlDuplicate('https://example.com/unique.mp3');

    expect($duplicate)->toBeNull();
});

test('find global url duplicate returns null for empty url', function () {
    $duplicate = DuplicateDetectionService::findGlobalUrlDuplicate('');

    expect($duplicate)->toBeNull();
});

test('analyze file upload with user duplicate', function () {
    Storage::fake('public');
    
    $user = User::factory()->create();
    $filePath = 'media/test-audio.mp3';
    Storage::disk('public')->put($filePath, 'test content');

    $mediaFile = MediaFile::factory()->create([
        'user_id' => $user->id,
        'file_hash' => hash('sha256', 'test content'),
    ]);

    $libraryItem = LibraryItem::factory()->create([
        'user_id' => $user->id,
        'media_file_id' => $mediaFile->id,
    ]);

    $result = DuplicateDetectionService::analyzeFileUpload($filePath, $user->id);

    expect($result['is_user_duplicate'])->toBeTrue();
    expect($result['is_global_duplicate'])->toBeTrue();
    expect($result['user_duplicate_media_file'])->toBe($mediaFile);
    expect($result['should_link_to_user_duplicate'])->toBeTrue();
    expect($result['should_create_new_file'])->toBeFalse();
});

test('analyze file upload with global duplicate only', function () {
    Storage::fake('public');
    
    $user1 = User::factory()->create();
    $user2 = User::factory()->create();
    $filePath = 'media/test-audio.mp3';
    Storage::disk('public')->put($filePath, 'test content');

    $mediaFile = MediaFile::factory()->create([
        'user_id' => $user1->id,
        'file_hash' => hash('sha256', 'test content'),
    ]);

    $result = DuplicateDetectionService::analyzeFileUpload($filePath, $user2->id);

    expect($result['is_user_duplicate'])->toBeFalse();
    expect($result['is_global_duplicate'])->toBeTrue();
    expect($result['global_duplicate_media_file'])->toBe($mediaFile);
    expect($result['should_link_to_global_duplicate'])->toBeTrue();
    expect($result['should_create_new_file'])->toBeFalse();
});

test('analyze file upload with no duplicates', function () {
    Storage::fake('public');
    
    $user = User::factory()->create();
    $filePath = 'media/unique-audio.mp3';
    Storage::disk('public')->put($filePath, 'unique content');

    $result = DuplicateDetectionService::analyzeFileUpload($filePath, $user->id);

    expect($result['is_user_duplicate'])->toBeFalse();
    expect($result['is_global_duplicate'])->toBeFalse();
    expect($result['should_link_to_user_duplicate'])->toBeFalse();
    expect($result['should_link_to_global_duplicate'])->toBeFalse();
    expect($result['should_create_new_file'])->toBeTrue();
});

test('analyze url source with user duplicate', function () {
    $user = User::factory()->create();
    $sourceUrl = 'https://example.com/audio.mp3';

    $existingItem = LibraryItem::factory()->create([
        'user_id' => $user->id,
        'source_url' => $sourceUrl,
        'source_type' => 'url',
    ]);

    $result = DuplicateDetectionService::analyzeUrlSource($sourceUrl, $user->id);

    expect($result['is_user_duplicate'])->toBeTrue();
    expect($result['is_global_duplicate'])->toBeTrue();
    expect($result['user_duplicate_library_item'])->toBe($existingItem);
    expect($result['should_link_to_user_duplicate'])->toBeTrue();
    expect($result['should_create_new_file'])->toBeFalse();
});

test('analyze url source with global duplicate', function () {
    $user1 = User::factory()->create();
    $user2 = User::factory()->create();
    $sourceUrl = 'https://example.com/audio.mp3';

    $mediaFile = MediaFile::factory()->create([
        'user_id' => $user1->id,
        'source_url' => $sourceUrl,
        'file_hash' => 'test-hash',
    ]);

    $result = DuplicateDetectionService::analyzeUrlSource($sourceUrl, $user2->id);

    expect($result['is_user_duplicate'])->toBeFalse();
    expect($result['is_global_duplicate'])->toBeTrue();
    expect($result['global_duplicate_media_file'])->toBe($mediaFile);
    expect($result['should_link_to_global_duplicate'])->toBeTrue();
    expect($result['should_create_new_file'])->toBeFalse();
});

test('analyze url source with no duplicates', function () {
    $user = User::factory()->create();
    $sourceUrl = 'https://example.com/new-audio.mp3';

    $result = DuplicateDetectionService::analyzeUrlSource($sourceUrl, $user->id);

    expect($result['is_user_duplicate'])->toBeFalse();
    expect($result['is_global_duplicate'])->toBeFalse();
    expect($result['should_link_to_user_duplicate'])->toBeFalse();
    expect($result['should_link_to_global_duplicate'])->toBeFalse();
    expect($result['should_create_new_file'])->toBeTrue();
});

test('analyze url source excludes current library item', function () {
    $user = User::factory()->create();
    $sourceUrl = 'https://example.com/audio.mp3';

    $libraryItem = LibraryItem::factory()->create([
        'user_id' => $user->id,
        'source_url' => $sourceUrl,
        'source_type' => 'url',
    ]);

    $result = DuplicateDetectionService::analyzeUrlSource($sourceUrl, $user->id, $libraryItem->id);

    expect($result['is_user_duplicate'])->toBeFalse();
    expect($result['is_global_duplicate'])->toBeFalse();
    expect($result['should_create_new_file'])->toBeTrue();
});

test('analyze url source detects user media file only edge case', function () {
    $user = User::factory()->create();
    $sourceUrl = 'https://example.com/audio.mp3';

    $mediaFile = MediaFile::factory()->create([
        'user_id' => $user->id,
        'source_url' => $sourceUrl,
        'file_hash' => 'test-hash',
    ]);

    $result = DuplicateDetectionService::analyzeUrlSource($sourceUrl, $user->id);

    expect($result['is_user_duplicate'])->toBeTrue();
    expect($result['user_media_file_only'])->toBeTrue();
    expect($result['should_link_to_user_media_file'])->toBeTrue();
});

test('analyze url source handles empty url', function () {
    $user = User::factory()->create();

    $result = DuplicateDetectionService::analyzeUrlSource('', $user->id);

    expect($result['is_user_duplicate'])->toBeFalse();
    expect($result['is_global_duplicate'])->toBeFalse();
    expect($result['should_create_new_file'])->toBeTrue();
});


afterEach(function () {
    Storage::fake('public');
});

test('calculate file hash from existing file', function () {
    $filePath = 'media/test-audio.mp3';
    $content = 'test audio content for hashing';
    Storage::disk('public')->put($filePath, $content);

    $expectedHash = hash('sha256', $content);
    $hash = DuplicateDetectionService::calculateFileHash($filePath);

    expect($hash)->toBe($expectedHash);
});

test('calculate file hash returns null for non-existent file', function () {
    $hash = DuplicateDetectionService::calculateFileHash('media/non-existent.mp3');

    expect($hash)->toBeNull();
});

test('find global duplicate by file hash', function () {
    $filePath = 'media/test-audio.mp3';
    Storage::disk('public')->put($filePath, 'test content');

    $mediaFile = MediaFile::factory()->create([
        'file_path' => 'media/different-file.mp3',
        'file_hash' => hash('sha256', 'test content'),
    ]);

    $duplicate = DuplicateDetectionService::findGlobalDuplicate($filePath);

    expect($duplicate)->toBe($mediaFile);
    expect($duplicate->id)->toBe($mediaFile->id);
});

test('find global duplicate returns null when no duplicate exists', function () {
    $filePath = 'media/unique-audio.mp3';
    Storage::disk('public')->put($filePath, 'unique content');

    $duplicate = DuplicateDetectionService::findGlobalDuplicate($filePath);

    expect($duplicate)->toBeNull();
});

test('find user duplicate by file hash', function () {
    $user = User::factory()->create();
    $filePath = 'media/test-audio.mp3';
    Storage::disk('public')->put($filePath, 'test content');

    $mediaFile = MediaFile::factory()->create([
        'user_id' => $user->id,
        'file_path' => 'media/different-file.mp3',
        'file_hash' => hash('sha256', 'test content'),
    ]);

    $libraryItem = LibraryItem::factory()->create([
        'user_id' => $user->id,
        'media_file_id' => $mediaFile->id,
    ]);

    $duplicate = DuplicateDetectionService::findUserDuplicate($filePath, $user->id);

    expect($duplicate)->toBe($mediaFile);
    expect($duplicate->id)->toBe($mediaFile->id);
});

test('find user duplicate returns null when file does not exist', function () {
    $user = User::factory()->create();

    $duplicate = DuplicateDetectionService::findUserDuplicate('media/non-existent.mp3', $user->id);

    expect($duplicate)->toBeNull();
});

test('find url duplicate for user', function () {
    $user = User::factory()->create();
    $sourceUrl = 'https://example.com/audio.mp3';

    $existingItem = LibraryItem::factory()->create([
        'user_id' => $user->id,
        'source_url' => $sourceUrl,
        'source_type' => 'url',
    ]);

    $duplicate = DuplicateDetectionService::findUrlDuplicateForUser($sourceUrl, $user->id);

    expect($duplicate)->toBe($existingItem);
    expect($duplicate->id)->toBe($existingItem->id);
});

test('find url duplicate for user returns null when no duplicate', function () {
    $user = User::factory()->create();

    $duplicate = DuplicateDetectionService::findUrlDuplicateForUser('https://example.com/new-audio.mp3', $user->id);

    expect($duplicate)->toBeNull();
});

test('find url duplicate for user returns null for empty url', function () {
    $user = User::factory()->create();

    $duplicate = DuplicateDetectionService::findUrlDuplicateForUser('', $user->id);

    expect($duplicate)->toBeNull();
});

test('find global url duplicate', function () {
    $user1 = User::factory()->create();
    $sourceUrl = 'https://example.com/audio.mp3';

    $mediaFile = MediaFile::factory()->create([
        'user_id' => $user1->id,
        'source_url' => $sourceUrl,
        'file_hash' => 'test-hash-123',
    ]);

    $duplicate = DuplicateDetectionService::findGlobalUrlDuplicate($sourceUrl);

    expect($duplicate)->toBe($mediaFile);
});

test('find global url duplicate returns null when no duplicate', function () {
    $duplicate = DuplicateDetectionService::findGlobalUrlDuplicate('https://example.com/unique.mp3');

    expect($duplicate)->toBeNull();
});

test('find global url duplicate returns null for empty url', function () {
    $duplicate = DuplicateDetectionService::findGlobalUrlDuplicate('');

    expect($duplicate)->toBeNull();
});

test('analyze file upload with user duplicate', function () {
    $user = User::factory()->create();
    $filePath = 'media/test-audio.mp3';
    Storage::disk('public')->put($filePath, 'test content');

    $mediaFile = MediaFile::factory()->create([
        'user_id' => $user->id,
        'file_hash' => hash('sha256', 'test content'),
    ]);

    $libraryItem = LibraryItem::factory()->create([
        'user_id' => $user->id,
        'media_file_id' => $mediaFile->id,
    ]);

    $result = DuplicateDetectionService::analyzeFileUpload($filePath, $user->id);

    expect($result['is_user_duplicate'])->toBeTrue();
    expect($result['is_global_duplicate'])->toBeTrue();
    expect($result['user_duplicate_media_file'])->toBe($mediaFile);
    expect($result['should_link_to_user_duplicate'])->toBeTrue();
    expect($result['should_create_new_file'])->toBeFalse();
});

test('analyze file upload with global duplicate only', function () {
    $user1 = User::factory()->create();
    $user2 = User::factory()->create();
    $filePath = 'media/test-audio.mp3';
    Storage::disk('public')->put($filePath, 'test content');

    $mediaFile = MediaFile::factory()->create([
        'user_id' => $user1->id,
        'file_hash' => hash('sha256', 'test content'),
    ]);

    $result = DuplicateDetectionService::analyzeFileUpload($filePath, $user2->id);

    expect($result['is_user_duplicate'])->toBeFalse();
    expect($result['is_global_duplicate'])->toBeTrue();
    expect($result['global_duplicate_media_file'])->toBe($mediaFile);
    expect($result['should_link_to_global_duplicate'])->toBeTrue();
    expect($result['should_create_new_file'])->toBeFalse();
});

test('analyze file upload with no duplicates', function () {
    $user = User::factory()->create();
    $filePath = 'media/unique-audio.mp3';
    Storage::disk('public')->put($filePath, 'unique content');

    $result = DuplicateDetectionService::analyzeFileUpload($filePath, $user->id);

    expect($result['is_user_duplicate'])->toBeFalse();
    expect($result['is_global_duplicate'])->toBeFalse();
    expect($result['should_link_to_user_duplicate'])->toBeFalse();
    expect($result['should_link_to_global_duplicate'])->toBeFalse();
    expect($result['should_create_new_file'])->toBeTrue();
});

test('analyze url source with user duplicate', function () {
    $user = User::factory()->create();
    $sourceUrl = 'https://example.com/audio.mp3';

    $existingItem = LibraryItem::factory()->create([
        'user_id' => $user->id,
        'source_url' => $sourceUrl,
        'source_type' => 'url',
    ]);

    $result = DuplicateDetectionService::analyzeUrlSource($sourceUrl, $user->id);

    expect($result['is_user_duplicate'])->toBeTrue();
    expect($result['is_global_duplicate'])->toBeTrue();
    expect($result['user_duplicate_library_item'])->toBe($existingItem);
    expect($result['should_link_to_user_duplicate'])->toBeTrue();
    expect($result['should_create_new_file'])->toBeFalse();
});

test('analyze url source with global duplicate', function () {
    $user1 = User::factory()->create();
    $user2 = User::factory()->create();
    $sourceUrl = 'https://example.com/audio.mp3';

    $mediaFile = MediaFile::factory()->create([
        'user_id' => $user1->id,
        'source_url' => $sourceUrl,
        'file_hash' => 'test-hash',
    ]);

    $result = DuplicateDetectionService::analyzeUrlSource($sourceUrl, $user2->id);

    expect($result['is_user_duplicate'])->toBeFalse();
    expect($result['is_global_duplicate'])->toBeTrue();
    expect($result['global_duplicate_media_file'])->toBe($mediaFile);
    expect($result['should_link_to_global_duplicate'])->toBeTrue();
    expect($result['should_create_new_file'])->toBeFalse();
});

test('analyze url source with no duplicates', function () {
    $user = User::factory()->create();
    $sourceUrl = 'https://example.com/new-audio.mp3';

    $result = DuplicateDetectionService::analyzeUrlSource($sourceUrl, $user->id);

    expect($result['is_user_duplicate'])->toBeFalse();
    expect($result['is_global_duplicate'])->toBeFalse();
    expect($result['should_link_to_user_duplicate'])->toBeFalse();
    expect($result['should_link_to_global_duplicate'])->toBeFalse();
    expect($result['should_create_new_file'])->toBeTrue();
});

test('analyze url source excludes current library item', function () {
    $user = User::factory()->create();
    $sourceUrl = 'https://example.com/audio.mp3';

    $libraryItem = LibraryItem::factory()->create([
        'user_id' => $user->id,
        'source_url' => $sourceUrl,
        'source_type' => 'url',
    ]);

    $result = DuplicateDetectionService::analyzeUrlSource($sourceUrl, $user->id, $libraryItem->id);

    expect($result['is_user_duplicate'])->toBeFalse();
    expect($result['is_global_duplicate'])->toBeFalse();
    expect($result['should_create_new_file'])->toBeTrue();
});

test('analyze url source detects user media file only edge case', function () {
    $user = User::factory()->create();
    $sourceUrl = 'https://example.com/audio.mp3';

    $mediaFile = MediaFile::factory()->create([
        'user_id' => $user->id,
        'source_url' => $sourceUrl,
        'file_hash' => 'test-hash',
    ]);

    $result = DuplicateDetectionService::analyzeUrlSource($sourceUrl, $user->id);

    expect($result['is_user_duplicate'])->toBeTrue();
    expect($result['user_media_file_only'])->toBeTrue();
    expect($result['should_link_to_user_media_file'])->toBeTrue();
});

test('analyze url source handles empty url', function () {
    $user = User::factory()->create();

    $result = DuplicateDetectionService::analyzeUrlSource('', $user->id);

    expect($result['is_user_duplicate'])->toBeFalse();
    expect($result['is_global_duplicate'])->toBeFalse();
    expect($result['should_create_new_file'])->toBeTrue();
});
