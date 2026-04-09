<?php

use App\Services\MediaProcessing\MediaStorageManager;
use Illuminate\Support\Facades\Storage;

uses()->group('Unit', 'MediaStorageManager');

beforeEach(function () {
    Storage::fake('public');
});

test('stores file with hash-based naming', function () {
    $manager = app(MediaStorageManager::class);
    $content = 'test content';
    $extension = 'mp3';
    $expectedHash = hash('sha256', $content);

    $result = $manager->storeFile($content, $extension);

    expect($result['file_path'])->toBe('media/'.$expectedHash.'.'.$extension);
    expect($result['file_hash'])->toBe($expectedHash);
    expect($result['filesize'])->toBe(strlen($content));
    Storage::disk('public')->assertExists($result['file_path']);
});

test('stores file with source url', function () {
    $manager = app(MediaStorageManager::class);
    $content = 'test content';
    $extension = 'mp3';
    $sourceUrl = 'https://example.com/audio.mp3';

    $result = $manager->storeFile($content, $extension, $sourceUrl);

    expect($result['source_url'])->toBe($sourceUrl);
    Storage::disk('public')->assertExists($result['file_path']);
});

test('cleans up temp file', function () {
    $manager = app(MediaStorageManager::class);
    $tempPath = 'temp-uploads/test-file.mp3';
    Storage::disk('public')->put($tempPath, 'test content');

    $manager->cleanupTempFile($tempPath);

    Storage::disk('public')->assertMissing($tempPath);
});

test('gets file size from storage', function () {
    $manager = app(MediaStorageManager::class);
    $content = 'test content for size check';
    $filePath = 'media/test-file.mp3';
    Storage::disk('public')->put($filePath, $content);

    $size = $manager->getFileSize($filePath);

    expect($size)->toBe(strlen($content));
});

test('returns 0 for non-existent file size', function () {
    $manager = app(MediaStorageManager::class);

    $size = $manager->getFileSize('media/non-existent.mp3');

    expect($size)->toBe(0);
});

test('checks if file exists', function () {
    $manager = app(MediaStorageManager::class);
    $filePath = 'media/test-file.mp3';
    Storage::disk('public')->put($filePath, 'test content');

    $exists = $manager->fileExists($filePath);

    expect($exists)->toBeTrue();
});

test('returns false for non-existent file', function () {
    $manager = app(MediaStorageManager::class);

    $exists = $manager->fileExists('media/non-existent.mp3');

    expect($exists)->toBeFalse();
});

test('handles file extension correctly', function () {
    $manager = app(MediaStorageManager::class);
    $content = 'test content';
    
    $resultMp3 = $manager->storeFile($content, 'mp3');
    $resultWav = $manager->storeFile($content, 'wav');

    expect($resultMp3['file_path'])->toEndWith('.mp3');
    expect($resultWav['file_path'])->toEndWith('.wav');
});

test('calculates correct file size', function () {
    $manager = app(MediaStorageManager::class);
    $content = str_repeat('x', 1000);
    $extension = 'mp3';

    $result = $manager->storeFile($content, $extension);

    expect($result['filesize'])->toBe(1000);
});

test('generates unique hash for different content', function () {
    $manager = app(MediaStorageManager::class);
    $content1 = 'content one';
    $content2 = 'content two';

    $result1 = $manager->storeFile($content1, 'mp3');
    $result2 = $manager->storeFile($content2, 'mp3');

    expect($result1['file_hash'])->not->toBe($result2['file_hash']);
    expect($result1['file_path'])->not->toBe($result2['file_path']);
});

test('generates same hash for identical content', function () {
    $manager = app(MediaStorageManager::class);
    $content = 'identical content';

    $result1 = $manager->storeFile($content, 'mp3');
    $result2 = $manager->storeFile($content, 'mp3');

    expect($result1['file_hash'])->toBe($result2['file_hash']);
    expect($result1['file_path'])->toBe($result2['file_path']);
});

