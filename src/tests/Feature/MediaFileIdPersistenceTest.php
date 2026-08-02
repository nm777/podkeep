<?php

use App\Jobs\ProcessMediaFile;
use App\Models\LibraryItem;
use App\Models\MediaFile;
use App\Models\User;
use App\ProcessingStatusType;
use App\Services\MediaProcessing\MediaProcessingService;
use App\Services\MediaProcessing\MediaStorageManager;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Storage;

describe('media_file_id persistence', function () {
    beforeEach(function () {
        Storage::fake('media');
        Queue::fake();
        $this->user = User::factory()->create();
    });

    it('persists media_file_id after processing a URL download', function () {
        $mp3Content = 'ID3'.str_repeat("\x00", 100);

        Http::fake([
            'https://example.com/test-persist.mp3' => Http::response($mp3Content, 200, [
                'Content-Type' => 'audio/mpeg',
            ]),
        ]);

        $libraryItem = LibraryItem::factory()->create([
            'user_id' => $this->user->id,
            'source_type' => 'url',
            'source_url' => 'https://example.com/test-persist.mp3',
            'processing_status' => ProcessingStatusType::PENDING,
        ]);

        $job = new ProcessMediaFile($libraryItem, 'https://example.com/test-persist.mp3', null);
        $job->handle(app(MediaProcessingService::class));

        $libraryItem->refresh();

        expect($libraryItem->processing_status)->toBe(ProcessingStatusType::COMPLETED);
        expect($libraryItem->media_file_id)->not->toBeNull();

        $fromDb = LibraryItem::find($libraryItem->id);
        expect($fromDb->media_file_id)->not->toBeNull();
        expect($fromDb->media_file_id)->toBe($libraryItem->media_file_id);
    });

    it('persists media_file_id after processing a file upload', function () {
        $content = 'ID3'.str_repeat("\x00", 100);
        $tempPath = 'temp-uploads/test-upload.mp3';
        Storage::disk('media')->put($tempPath, $content);

        $libraryItem = LibraryItem::factory()->create([
            'user_id' => $this->user->id,
            'source_type' => 'upload',
            'processing_status' => ProcessingStatusType::PENDING,
        ]);

        $service = app(MediaProcessingService::class);
        $result = $service->processFromFile($libraryItem, $tempPath);

        expect($result['media_file'])->not->toBeNull();

        $fromDb = LibraryItem::find($libraryItem->id);
        expect($fromDb->media_file_id)->not->toBeNull();
        expect($fromDb->media_file_id)->toBe($result['media_file']->id);
    });

    it('persists media_file_id when linking to user duplicate', function () {
        $content = 'ID3'.str_repeat("\x00", 100);
        $hash = hash('sha256', $content);

        $existingMediaFile = MediaFile::factory()->create([
            'user_id' => $this->user->id,
            'file_hash' => $hash,
            'file_path' => 'media/existing-user-dup.mp3',
        ]);

        Storage::disk('media')->put('media/existing-user-dup.mp3', $content);

        LibraryItem::factory()->create([
            'user_id' => $this->user->id,
            'media_file_id' => $existingMediaFile->id,
        ]);

        $tempPath = 'temp-uploads/dup-upload.mp3';
        Storage::disk('media')->put($tempPath, $content);

        $libraryItem = LibraryItem::factory()->create([
            'user_id' => $this->user->id,
            'source_type' => 'upload',
            'processing_status' => ProcessingStatusType::PENDING,
        ]);

        $service = app(MediaProcessingService::class);
        $result = $service->processFromFile($libraryItem, $tempPath);

        expect($result['media_file'])->not->toBeNull();
        expect($result['is_duplicate'])->toBeTrue();

        $fromDb = LibraryItem::find($libraryItem->id);
        expect($fromDb->media_file_id)->not->toBeNull();
        expect($fromDb->media_file_id)->toBe($existingMediaFile->id);
    });

    it('persists media_file_id when linking to global duplicate', function () {
        $content = 'ID3'.str_repeat("\x00", 100);
        $hash = hash('sha256', $content);

        $otherUser = User::factory()->create();
        $existingMediaFile = MediaFile::factory()->create([
            'user_id' => $otherUser->id,
            'file_hash' => $hash,
            'file_path' => 'media/existing-global-dup.mp3',
        ]);

        Storage::disk('media')->put('media/existing-global-dup.mp3', $content);

        LibraryItem::factory()->create([
            'user_id' => $otherUser->id,
            'media_file_id' => $existingMediaFile->id,
        ]);

        $tempPath = 'temp-uploads/global-dup.mp3';
        Storage::disk('media')->put($tempPath, $content);

        $libraryItem = LibraryItem::factory()->create([
            'user_id' => $this->user->id,
            'source_type' => 'upload',
            'processing_status' => ProcessingStatusType::PENDING,
        ]);

        $service = app(MediaProcessingService::class);
        $result = $service->processFromFile($libraryItem, $tempPath);

        expect($result['media_file'])->not->toBeNull();

        $fromDb = LibraryItem::find($libraryItem->id);
        expect($fromDb->media_file_id)->not->toBeNull();
        expect($fromDb->media_file_id)->toBe($existingMediaFile->id);
    });

    it('links to the winning media file after a hash creation race', function (string $winningExtension, string $losingExtension) {
        $content = 'ID3'.str_repeat("\x00", 100);
        $hash = hash('sha256', $content);
        $tempPath = 'temp-uploads/race.mp3';
        $winningPath = 'media/'.$hash.'.'.$winningExtension;
        $losingPath = 'media/'.$hash.'.'.$losingExtension;
        Storage::disk('media')->put($tempPath, $content);

        $owner = User::factory()->create();
        $libraryItem = LibraryItem::factory()->create([
            'user_id' => $this->user->id,
            'source_type' => 'upload',
            'processing_status' => \App\Enums\ProcessingStatusType::PENDING,
        ]);

        $storageManager = $this->mock(MediaStorageManager::class);
        $storageManager->shouldReceive('fileExists')
            ->once()
            ->with($tempPath)
            ->andReturnTrue();
        $storageManager->shouldReceive('moveTempFile')
            ->once()
            ->with($tempPath, null)
            ->andReturnUsing(function () use ($content, $hash, $losingPath, $owner, $tempPath, $winningPath): array {
                Storage::disk('media')->delete($tempPath);
                Storage::disk('media')->put($winningPath, $content);
                MediaFile::factory()->create([
                    'user_id' => $owner->id,
                    'file_path' => $winningPath,
                    'file_hash' => $hash,
                    'mime_type' => 'audio/mpeg',
                    'filesize' => strlen($content),
                ]);
                Storage::disk('media')->put($losingPath, $content);

                return [
                    'file_path' => $losingPath,
                    'file_hash' => $hash,
                    'mime_type' => 'audio/mpeg',
                    'filesize' => strlen($content),
                ];
            });

        $result = app(MediaProcessingService::class)->processFromFile($libraryItem, $tempPath);

        $libraryItem->refresh();

        expect($result['is_duplicate'])->toBeFalse();
        expect($result['media_file']->file_path)->toBe($winningPath);
        expect($libraryItem->processing_status)->toBe(\App\Enums\ProcessingStatusType::COMPLETED);
        expect($libraryItem->media_file_id)->toBe($result['media_file']->id);
        Storage::disk('media')->assertExists($winningPath);
        if ($losingPath !== $winningPath) {
            Storage::disk('media')->assertMissing($losingPath);
        }
    })->with([
        'shared path' => ['mp3', 'mp3'],
        'losing path' => ['mp3', 'wav'],
    ]);
});
