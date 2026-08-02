<?php

namespace App\Console\Commands;

use App\Models\MediaFile;
use App\Services\MediaProcessing\MediaValidator;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class ProbeMediaDurations extends Command
{
    /** @var string */
    protected $signature = 'media:probe-durations';

    /** @var string */
    protected $description = 'Backfill media_files.duration via ffprobe for any files missing it.';

    public function handle(MediaValidator $validator): int
    {
        $files = MediaFile::whereNull('duration')->orWhere('duration', 0)->get();

        if ($files->isEmpty()) {
            $this->info('All media files already have a duration.');

            return self::SUCCESS;
        }

        $updated = 0;
        $missing = 0;
        $bar = $this->output->createProgressBar($files->count());
        $bar->start();

        foreach ($files as $mediaFile) {
            $path = Storage::disk('media')->path($mediaFile->file_path);

            if (! file_exists($path)) {
                $missing++;
                $bar->advance();
                continue;
            }

            $duration = $validator->probeDuration($path);

            if ($duration !== null) {
                $mediaFile->update(['duration' => $duration]);
                $updated++;
            } else {
                $missing++;
            }

            $bar->advance();
        }

        $bar->finish();
        $this->newLine();
        $this->info("Probed {$files->count()} files; updated {$updated}; could not read {$missing}.");

        return self::SUCCESS;
    }
}
