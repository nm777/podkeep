<?php

namespace App\Jobs;

use App\Enums\ProcessingStatusType;
use App\Models\LibraryItem;
use App\Services\MediaProcessing\MediaRedownloader;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\Middleware\WithoutOverlapping;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class RedownloadMediaFile implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    public int $timeout = 300;

    public function __construct(
        private LibraryItem $libraryItem,
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
        return [(new WithoutOverlapping('media-file-'.$this->libraryItem->media_file_id))
            ->expireAfter(360)
            ->dontRelease()];
    }

    public function handle(MediaRedownloader $redownloader): void
    {
        $libraryItem = $this->libraryItem->refresh();

        if (! $libraryItem->mediaFile || ! $libraryItem->mediaFile->source_url) {
            $this->failRedownload();

            return;
        }

        try {
            $result = $redownloader->redownload($libraryItem);
        } catch (\InvalidArgumentException $e) {
            $this->failRedownload();

            return;
        }

        $libraryItem->update([
            'processing_status' => ProcessingStatusType::COMPLETED,
            'processing_completed_at' => now(),
            'processing_error' => null,
        ]);

        Log::info('Media file redownloaded successfully', [
            'library_item_id' => $libraryItem->id,
            'media_file_id' => $libraryItem->mediaFile->id,
            'hash_changed' => $result['hash_changed'],
        ]);
    }

    public function failed(?\Throwable $exception): void
    {
        $this->failRedownload();
    }

    private function failRedownload(): void
    {
        LibraryItem::whereKey($this->libraryItem->id)
            ->where('processing_status', '!=', ProcessingStatusType::COMPLETED)
            ->update([
                'processing_status' => ProcessingStatusType::FAILED,
                'processing_completed_at' => now(),
                'processing_error' => 'Media redownload failed.',
            ]);

        Log::error('Media redownload job failed', [
            'library_item_id' => $this->libraryItem->id,
            'error_code' => 'media_redownload_failed',
            'message' => 'Media redownload failed.',
        ]);
    }
}
