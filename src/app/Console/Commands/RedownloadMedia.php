<?php

namespace App\Console\Commands;

use App\Models\LibraryItem;
use App\Services\MediaProcessing\MediaRedownloader;
use Illuminate\Console\Command;

class RedownloadMedia extends Command
{
    protected $signature = 'media:redownload 
                            {--id= : Specific library item ID to redownload}
                            {--user-id= : Redownload all media for a specific user}
                            {--missing-only : Only redownload media where the file is missing}
                            {--dry-run : Show what would be redownloaded without actually doing it}';

    protected $description = 'Redownload media files from their original source URLs';

    public function handle()
    {
        $query = LibraryItem::with('mediaFile')->whereHas('mediaFile');

        if ($this->option('id')) {
            $query->where('id', $this->option('id'));
        }

        if ($this->option('user-id')) {
            $query->where('user_id', $this->option('user-id'));
        }

        $items = $query->get();

        if ($items->isEmpty()) {
            $this->info('No library items found matching the criteria.');

            return Command::SUCCESS;
        }

        $this->info("Found {$items->count()} library item(s) to process.");

        $redownloader = new MediaRedownloader(
            new \App\Services\MediaProcessing\MediaDownloader,
            new \App\Services\MediaProcessing\MediaStorageManager,
        );

        $successCount = 0;
        $failureCount = 0;
        $skippedCount = 0;

        foreach ($items as $item) {
            $title = $item->title ?? 'Untitled';
            $this->line("Processing item #{$item->id}: {$title}");

            if (! $item->mediaFile || ! $item->mediaFile->source_url) {
                $this->warn('  Skipped: No source URL available');
                $skippedCount++;
                continue;
            }

            if ($this->option('missing-only')) {
                $storageManager = new \App\Services\MediaProcessing\MediaStorageManager;
                if ($item->mediaFile && $storageManager->fileExists($item->mediaFile->file_path)) {
                    $this->line('  Skipped: File exists (missing-only mode)');
                    $skippedCount++;
                    continue;
                }
            }

            if ($this->option('dry-run')) {
                $sourceUrl = $item->mediaFile->source_url ?? 'N/A';
                $this->line("  Would redownload from: {$sourceUrl}");
                $skippedCount++;
                continue;
            }

            try {
                $result = $redownloader->redownload($item);
                $successCount++;

                $message = "  Success: File redownloaded";
                if ($result['hash_changed']) {
                    $message .= ' (content has changed)';
                }
                $this->info($message);
            } catch (\Exception $e) {
                $failureCount++;
                $this->error("  Failed: {$e->getMessage()}");
            }
        }

        $this->newLine();
        $this->info('Summary:');
        $this->line("  Success: {$successCount}");
        $this->line("  Failed: {$failureCount}");
        $this->line("  Skipped: {$skippedCount}");

        return $failureCount > 0 ? Command::FAILURE : Command::SUCCESS;
    }
}
