<?php

namespace App\Console\Commands;

use App\Models\MediaFile;
use App\Services\MediaProcessing\VideoToAudioConverter;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class AddMp3SeekIndexes extends Command
{
    /** @var string */
    protected $signature = 'media:add-mp3-seek-indexes';

    /** @var string */
    protected $description = 'Add seek indexes to existing MP3 files.';

    public function handle(VideoToAudioConverter $converter): int
    {
        $files = MediaFile::query()->where('mime_type', 'audio/mpeg');
        $total = $files->count();

        if ($total === 0) {
            $this->info('No MP3 files found.');

            return self::SUCCESS;
        }

        $indexed = 0;
        $missing = 0;
        $bar = $this->output->createProgressBar($total);
        $bar->start();

        foreach ($files->lazyById() as $mediaFile) {
            $path = Storage::disk('media')->path($mediaFile->file_path);

            if (! file_exists($path)) {
                $missing++;
            } elseif ($converter->ensureMp3SeekIndex($mediaFile->file_path)) {
                $mediaFile->update([
                    'file_hash' => hash_file('sha256', $path),
                    'filesize' => filesize($path),
                ]);
                $indexed++;
            }

            $bar->advance();
        }

        $bar->finish();
        $this->newLine();
        $this->info("Processed {$total} MP3 files; indexed {$indexed}; missing {$missing}.");

        return self::SUCCESS;
    }
}
