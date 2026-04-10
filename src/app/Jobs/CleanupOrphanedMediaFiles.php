<?php

namespace App\Jobs;

use App\Models\MediaFile;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;

class CleanupOrphanedMediaFiles implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $totalDeleted = 0;

        MediaFile::whereDoesntHave('libraryItems')
            ->chunkById(100, function ($orphanedFiles) use (&$totalDeleted) {
                foreach ($orphanedFiles as $mediaFile) {
                    if ($mediaFile->file_path) {
                        Storage::disk('public')->delete($mediaFile->file_path);
                    }

                    $mediaFile->delete();
                    $totalDeleted++;
                }
            });

        if ($totalDeleted > 0) {
            \Log::info("Cleaned up {$totalDeleted} orphaned media files");
        }
    }
}
