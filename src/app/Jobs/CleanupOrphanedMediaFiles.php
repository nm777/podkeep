<?php

namespace App\Jobs;

use App\Models\MediaFile;
use App\Services\MediaFileRetirementService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class CleanupOrphanedMediaFiles implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $orphanedFiles = MediaFile::whereDoesntHave('libraryItems')->get();

        $deleted = 0;
        foreach ($orphanedFiles as $mediaFile) {
            if (MediaFileRetirementService::retire($mediaFile)) {
                $deleted++;
            }
        }

        if ($deleted > 0) {
            \Log::info("Cleaned up {$deleted} orphaned media files");
        }
    }
}
