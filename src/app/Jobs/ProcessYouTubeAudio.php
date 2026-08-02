<?php

namespace App\Jobs;

use App\Enums\ProcessingStatusType;
use App\Models\LibraryItem;
use App\Services\YouTube\YouTubeProcessingService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\Middleware\WithoutOverlapping;
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
     * @return array<int, WithoutOverlapping>
     */
    public function middleware(): array
    {
        $middleware = [(new WithoutOverlapping('library-item-'.$this->libraryItem->id))
            ->expireAfter(720)
            ->dontRelease()];

        if ($this->libraryItem->media_file_id) {
            $middleware[] = (new WithoutOverlapping('media-file-'.$this->libraryItem->media_file_id))
                ->expireAfter(720)
                ->dontRelease();
        }

        return $middleware;
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
        LibraryItem::whereKey($this->libraryItem->id)
            ->where('processing_status', '!=', ProcessingStatusType::COMPLETED)
            ->update([
                'processing_status' => ProcessingStatusType::FAILED,
                'processing_completed_at' => now(),
                'processing_error' => 'YouTube processing failed.',
            ]);
    }
}
