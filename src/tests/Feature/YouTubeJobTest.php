<?php

use App\Jobs\ProcessYouTubeAudio;
use App\Models\LibraryItem;
use App\Models\User;
use App\ProcessingStatusType;
use App\Services\MediaProcessing\UnifiedDuplicateProcessor;
use App\Services\YouTube\YouTubeDownloader;
use App\Services\YouTube\YouTubeFileProcessor;
use App\Services\YouTube\YouTubeMetadataExtractor;
use App\Services\YouTube\YouTubeProcessingService;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

it('logs only safe context when a YouTube job fails', function () {
    Storage::fake('public');

    $user = User::factory()->create();
    $libraryItem = LibraryItem::factory()->create([
        'user_id' => $user->id,
        'source_type' => 'youtube',
        'source_url' => 'https://www.youtube.com/watch?v=dQw4w9WgXcQ',
    ]);

    $sensitiveUrl = 'https://user:secret@example.test/watch?v=dQw4w9WgXcQ&token=secret';
    $job = new ProcessYouTubeAudio($libraryItem, $sensitiveUrl);

    Log::shouldReceive('info')->once()->with('ProcessYouTubeAudio job started', [
        'library_item_id' => $libraryItem->id,
    ]);
    Log::shouldReceive('error')->once()->with('ProcessYouTubeAudio job exception', [
        'library_item_id' => $libraryItem->id,
        'error' => 'YouTube processing failed',
    ]);

    // Mock the processing service to avoid actual YouTube processing
    $processingService = mock(YouTubeProcessingService::class);
    $processingService->shouldReceive('processYouTubeUrl')
        ->once()
        ->andThrow(new Exception("Test error: {$sensitiveUrl}"));

    $job->handle($processingService);

    $libraryItem->refresh();
    $this->assertStringContainsString($sensitiveUrl, $libraryItem->processing_error);
});

it('does not log sensitive YouTube processing data', function () {
    $logContexts = collect([
        'Jobs/ProcessYouTubeAudio.php',
        'Services/YouTube/YouTubeDownloader.php',
        'Services/YouTube/YouTubeMetadataExtractor.php',
        'Services/YouTube/YouTubeProcessingService.php',
        'Services/YouTube/YouTubeFileProcessor.php',
        'Services/YouTubeVideoInfoService.php',
        'Services/MediaProcessing/UnifiedDuplicateProcessor.php',
    ])->map(fn (string $path) => preg_match_all('/Log::(?:info|error|warning)\((?:.|\R)*?\]\);/', file_get_contents(app_path($path)), $matches) ? implode("\n", $matches[0]) : '')->implode("\n");

    foreach ([
        'youtube_url',
        'source_url',
        "'command'",
        "'output'",
        "'error_output'",
        "'error_trace'",
        'getOutput',
        'getErrorOutput',
        'getTraceAsString',
    ] as $sensitiveField) {
        $this->assertStringNotContainsString($sensitiveField, $logContexts);
    }
});

it('marks library item as failed when video ID extraction fails instead of deleting', function () {
    Storage::fake('public');

    $user = User::factory()->create();
    $libraryItem = LibraryItem::factory()->create([
        'user_id' => $user->id,
        'source_type' => 'youtube',
        'source_url' => 'https://www.youtube.com/invalid',
    ]);

    $service = app(YouTubeProcessingService::class);

    $result = $service->processYouTubeUrl($libraryItem, 'https://www.youtube.com/invalid');

    expect($result['success'])->toBeFalse();
    expect($result['error'])->toBe('Invalid YouTube URL');

    $libraryItem->refresh();
    expect($libraryItem)->not->toBeNull();
    expect($libraryItem->processing_status)->toBe(ProcessingStatusType::FAILED);
    expect($libraryItem->processing_error)->not->toBeNull();
    expect($libraryItem->processing_completed_at)->not->toBeNull();

    $this->assertDatabaseHas('library_items', ['id' => $libraryItem->id]);
});

it('marks library item as failed when download fails instead of deleting', function () {
    Storage::fake('public');

    $user = User::factory()->create();
    $libraryItem = LibraryItem::factory()->create([
        'user_id' => $user->id,
        'source_type' => 'youtube',
        'source_url' => 'https://www.youtube.com/watch?v=dQw4w9WgXcQ',
    ]);

    $downloader = mock(YouTubeDownloader::class);
    $downloader->shouldReceive('downloadAudio')->andReturn(null);
    $downloader->shouldReceive('cleanupTempDirectory');

    $metadataExtractor = mock(YouTubeMetadataExtractor::class);
    $fileProcessor = mock(YouTubeFileProcessor::class);
    $duplicateProcessor = mock(UnifiedDuplicateProcessor::class);
    $duplicateProcessor->shouldReceive('processUrlDuplicate')->andReturn(['is_duplicate' => false]);

    $service = new YouTubeProcessingService($downloader, $metadataExtractor, $fileProcessor, $duplicateProcessor);
    $result = $service->processYouTubeUrl($libraryItem, 'https://www.youtube.com/watch?v=dQw4w9WgXcQ');

    expect($result['success'])->toBeFalse();
    expect($result['error'])->toBe('Failed to download YouTube video');

    $libraryItem->refresh();
    expect($libraryItem->processing_status)->toBe(ProcessingStatusType::FAILED);
    expect($libraryItem->processing_error)->not->toBeNull();
    $this->assertDatabaseHas('library_items', ['id' => $libraryItem->id]);
});
