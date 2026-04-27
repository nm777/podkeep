<?php

namespace App\Jobs;

use App\Models\LibraryItem;
use App\Models\MediaFile;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class CleanupDuplicateLibraryItem implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(public LibraryItem $libraryItem) {}

    public function handle(): void
    {
        $libraryItem = $this->libraryItem->fresh();

        if (! $libraryItem || ! $libraryItem->is_duplicate) {
            return;
        }

        $mediaFile = $libraryItem->mediaFile;

        if ($mediaFile) {
            $hasOtherValidItem = LibraryItem::where('media_file_id', $mediaFile->id)
                ->where('id', '!=', $libraryItem->id)
                ->where('is_duplicate', false)
                ->exists();

            if (! $hasOtherValidItem) {
                Log::info('Adopting duplicate library item as primary reference', [
                    'library_item_id' => $libraryItem->id,
                    'media_file_id' => $mediaFile->id,
                ]);

                $libraryItem->update([
                    'is_duplicate' => false,
                    'duplicate_detected_at' => null,
                ]);

                return;
            }
        }

        $libraryItem->delete();

        $this->cleanupOrphanedMediaFile($mediaFile);
    }

    private function cleanupOrphanedMediaFile(?MediaFile $mediaFile): void
    {
        if (! $mediaFile) {
            return;
        }

        $mediaFile->refresh();

        if ($mediaFile->libraryItems()->exists()) {
            return;
        }

        Log::info('Cleaning up orphaned media file after duplicate removal', [
            'media_file_id' => $mediaFile->id,
        ]);

        \Illuminate\Support\Facades\Storage::disk('public')->delete($mediaFile->file_path);
        $mediaFile->delete();
    }
}
