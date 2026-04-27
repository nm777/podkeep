<?php

namespace App\Jobs;

use App\Enums\ProcessingStatusType;
use App\Models\LibraryItem;
use App\Services\MediaProcessing\MediaRedownloader;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
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

    public function handle(MediaRedownloader $redownloader): void
    {
        $libraryItem = $this->libraryItem->refresh();

        if (! $libraryItem->mediaFile || ! $libraryItem->mediaFile->source_url) {
            $this->failRedownload('No source URL available for this media file');

            return;
        }

        try {
            $result = $redownloader->redownload($libraryItem);

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
        } catch (\Exception $e) {
            $this->failRedownload($e->getMessage());
        }
    }

    private function failRedownload(string $error): void
    {
        $this->libraryItem->update([
            'processing_status' => ProcessingStatusType::FAILED,
            'processing_completed_at' => now(),
            'processing_error' => $error,
        ]);

        Log::error('Media redownload job failed', [
            'library_item_id' => $this->libraryItem->id,
            'error' => $error,
        ]);
    }
}
