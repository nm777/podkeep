<?php

namespace App\Console\Commands;

use App\Jobs\RedownloadMediaFile;
use App\Models\Feed;
use App\Models\LibraryItem;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class RedownloadMissingMedia extends Command
{
    protected $signature = 'media:redownload-missing
                            {--feed= : Only redownload items for a specific feed (by slug or user_guid)}
                            {--limit= : Maximum number of items to redispatch}';

    protected $description = 'Re-download media files whose records exist but whose files are missing from disk';

    public function handle(): int
    {
        $query = LibraryItem::whereNotNull('media_file_id')
            ->whereHas('mediaFile');

        if ($feedIdentifier = $this->option('feed')) {
            $feed = Feed::where('slug', $feedIdentifier)
                ->orWhere('user_guid', $feedIdentifier)
                ->first();

            if (! $feed) {
                $this->error("Feed not found: {$feedIdentifier}");

                return Command::FAILURE;
            }

            $query->whereHas('feeds', fn ($q) => $q->where('feeds.id', $feed->id));
            $this->info("Scanning feed: {$feed->title}");
        }

        $items = $query->with('mediaFile')->get();
        $missing = $items->filter(
            fn (LibraryItem $item) => $item->mediaFile
                && ! Storage::disk('public')->exists($item->mediaFile->file_path)
        )->values();

        if ($missing->isEmpty()) {
            $this->info('No missing media files found.');

            return Command::SUCCESS;
        }

        $limit = (int) $this->option('limit');
        if ($limit > 0) {
            $missing = $missing->take($limit);
        }

        $this->info("Found {$missing->count()} missing media file(s). Dispatching redownload jobs...");

        foreach ($missing as $item) {
            RedownloadMediaFile::dispatch($item);
            $this->line("  - Item #{$item->id}: {$item->title}");
        }

        $this->info("Dispatched {$missing->count()} redownload job(s).");

        return Command::SUCCESS;
    }
}
