<?php

use App\ProcessingStatusType;
use App\Models\LibraryItem;
use App\Models\MediaFile;
use App\Models\User;
use App\Services\MediaProcessing\UnifiedDuplicateProcessor;
use App\Services\YouTube\YouTubeProcessingService;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

describe('YouTubeProcessingService duplicate handling', function () {
    it('does not re-download when processUrlDuplicate returns media_file with is_duplicate false', function () {
        $user = User::factory()->create();
        $libraryItem = LibraryItem::factory()->create([
            'user_id' => $user->id,
            'source_url' => 'https://www.youtube.com/watch?v=dQw4w9WgXcQ',
            'processing_status' => ProcessingStatusType::PENDING,
        ]);

        $mediaFile = MediaFile::factory()->create([
            'user_id' => $user->id,
            'source_url' => 'https://www.youtube.com/watch?v=dQw4w9WgXcQ',
        ]);

        $mockProcessor = Mockery::mock(UnifiedDuplicateProcessor::class);
        $mockProcessor->shouldReceive('processUrlDuplicate')
            ->once()
            ->andReturn([
                'success' => true,
                'is_duplicate' => false,
                'media_file' => $mediaFile,
                'message' => 'Linked to existing media file.',
            ]);

        $service = new YouTubeProcessingService(
            downloader: app(\App\Services\YouTube\YouTubeDownloader::class),
            metadataExtractor: app(\App\Services\YouTube\YouTubeMetadataExtractor::class),
            fileProcessor: app(\App\Services\YouTube\YouTubeFileProcessor::class),
            duplicateProcessor: $mockProcessor,
        );

        $result = $service->processYouTubeUrl($libraryItem, 'https://www.youtube.com/watch?v=dQw4w9WgXcQ');

        expect($result['success'])->toBeTrue();
        expect($result['media_file']->id)->toBe($mediaFile->id);
    });
});
