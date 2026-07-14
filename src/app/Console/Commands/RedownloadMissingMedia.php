<?php

namespace App\Console\Commands;

use App\Enums\ProcessingStatusType;
use App\Jobs\ProcessYouTubeAudio;
use App\Jobs\RedownloadMediaFile;
use App\Models\Feed;
use App\Models\LibraryItem;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class RedownloadMissingMedia extends Command
{
    protected $signature = 'media:redownload-missing
                            {--feed= : Only scan items for a specific feed (by slug or user_guid)}
                            {--limit= : Maximum number of items to redispatch}
                            {--dry-run : List missing files without dispatching jobs}';

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

        $recoverable = $missing->filter(fn (LibraryItem $item) => (bool) $item->mediaFile?->source_url)->values();
        $unrecoverable = $missing->reject(fn (LibraryItem $item) => (bool) $item->mediaFile?->source_url)->values();

        $this->info("Found {$missing->count()} missing media file(s).");
        $this->line("  Re-downloadable (has source_url): {$recoverable->count()}");
        $this->line("  Not recoverable (no source_url):  {$unrecoverable->count()}");

        if ($unrecoverable->isNotEmpty()) {
            $this->newLine();
            $this->warn('Not recoverable — these files were uploaded and have no source URL:');
            foreach ($unrecoverable as $item) {
                $this->line("  - Item #{$item->id} [{$item->source_type}]: {$item->title}");
            }
        }

        if ($this->option('dry-run')) {
            $this->newLine();
            $this->info('Re-downloadable files:');
            foreach ($recoverable as $item) {
                $this->line("  - Item #{$item->id} [{$item->source_type}]: {$item->title}");
            }
            $this->newLine();
            $this->info('Dry run complete. Re-run without --dry-run to dispatch redownload jobs.');

            return Command::SUCCESS;
        }

        $limit = (int) $this->option('limit');
        if ($limit > 0) {
            $recoverable = $recoverable->take($limit);
        }

        $this->newLine();
        $this->info("Dispatching {$recoverable->count()} redownload job(s)...");

        foreach ($recoverable as $item) {
            $item->update([
                'processing_status' => ProcessingStatusType::PROCESSING,
                'processing_started_at' => now(),
                'processing_completed_at' => null,
                'processing_error' => null,
            ]);

            if ($item->source_type === 'youtube') {
                ProcessYouTubeAudio::dispatch($item, $item->source_url);
            } else {
                RedownloadMediaFile::dispatch($item);
            }

            $this->line("  - Item #{$item->id} [{$item->source_type}]: {$item->title}");
        }

        $this->info("Dispatched {$recoverable->count()} redownload job(s). Check the queue worker and logs for results.");

        return Command::SUCCESS;
    }
}
