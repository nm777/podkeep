<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;

class RelocatePublicMedia extends Command
{
    protected $signature = 'media:relocate-public-storage';

    protected $description = 'Move media left on the public disk to private storage';

    public function handle(): int
    {
        $public = Storage::disk('public');
        $media = Storage::disk('media');
        $moved = 0;
        $skipped = 0;

        foreach (['media', 'temp-uploads', 'temp-youtube', 'temp-downloads'] as $directory) {
            foreach ($public->allFiles($directory) as $path) {
                if ($media->exists($path)) {
                    $skipped++;

                    continue;
                }

                File::ensureDirectoryExists(dirname($media->path($path)));

                if (File::move($public->path($path), $media->path($path))) {
                    $moved++;
                }
            }
        }

        $this->info("Relocated {$moved} media file(s); skipped {$skipped} existing destination(s).");

        return Command::SUCCESS;
    }
}
