<?php

namespace App\Jobs;

use App\Enums\ProcessingStatusType;
use App\Models\LibraryItem;
use App\Services\YouTube\YouTubeProcessingService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class ProcessYouTubeAudio implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $timeout = 360;

    public function __construct(
        protected LibraryItem $libraryItem,
        protected string $youtubeUrl,
    ) {}

    public function getLibraryItemId(): int
    {
        return $this->libraryItem->id;
    }

    /**
     * Execute the job.
     */
    public function handle(YouTubeProcessingService $processingService): void
    {
        Log::info('ProcessYouTubeAudio job started', [
            'library_item_id' => $this->libraryItem->id,
        ]);

        $result = $processingService->processYouTubeUrl($this->libraryItem, $this->youtubeUrl, $this->libraryItem->media_type->value);

        if (isset($result['success']) && $result['success'] === false) {
            Log::error('ProcessYouTubeAudio processing failed', [
                'library_item_id' => $this->libraryItem->id,
                'error' => 'YouTube processing failed',
            ]);
        } else {
            Log::info('ProcessYouTubeAudio completed successfully', [
                'library_item_id' => $this->libraryItem->id,
                'is_duplicate' => $result['is_duplicate'] ?? false,
            ]);
        }
    }

    public function failed(?\Throwable $exception): void
    {
        $this->libraryItem->update([
            'processing_status' => ProcessingStatusType::FAILED,
            'processing_completed_at' => now(),
            'processing_error' => 'YouTube processing failed.',
        ]);
    }
}
