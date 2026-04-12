<?php

namespace App\Jobs;

use App\Models\MediaFile;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

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
            DB::transaction(function () use ($mediaFile, &$deleted) {
                $locked = MediaFile::lockForUpdate()->find($mediaFile->id);

                if (! $locked || $locked->libraryItems()->exists()) {
                    return;
                }

                if ($locked->file_path) {
                    Storage::disk('public')->delete($locked->file_path);
                }

                $locked->delete();
                $deleted++;
            });
        }

        if ($deleted > 0) {
            \Log::info("Cleaned up {$deleted} orphaned media files");
        }
    }
}
