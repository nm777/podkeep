<?php

namespace App\Console\Commands;

use App\Enums\ProcessingStatusType;
use App\Jobs\ProcessMediaFile;
use App\Models\LibraryItem;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class RetryPendingProcessing extends Command
{
    protected $signature = 'media:retry-pending
                            {--minutes=5 : Consider items stuck if pending for longer than this many minutes}';

    protected $description = 'Re-dispatch processing jobs for items stuck in pending status';

    public function handle(): int
    {
        $minutes = (int) $this->option('minutes');
        $now = now();
        $cutoff = $now->copy()->subMinutes($minutes);

        $stuckItems = LibraryItem::where('processing_status', ProcessingStatusType::PENDING)
            ->where(function ($query) use ($cutoff) {
                $query->where('processing_started_at', '<', $cutoff)
                    ->orWhere(function ($query) use ($cutoff) {
                        $query->whereNull('processing_started_at')
                            ->where('created_at', '<', $cutoff);
                    });
            })
            ->get();

        if ($stuckItems->isEmpty()) {
            return Command::SUCCESS;
        }

        $redispatched = 0;
        $failed = 0;

        foreach ($stuckItems as $item) {
            $claimed = LibraryItem::whereKey($item)
                ->where('processing_status', ProcessingStatusType::PENDING)
                ->where(function ($query) use ($cutoff) {
                    $query->where('processing_started_at', '<', $cutoff)
                        ->orWhere(function ($query) use ($cutoff) {
                            $query->whereNull('processing_started_at')
                                ->where('created_at', '<', $cutoff);
                        });
                })
                ->update(['processing_started_at' => $now]);

            if ($claimed === 0) {
                continue;
            }

            if ($item->temp_file_path && Storage::disk('public')->exists($item->temp_file_path)) {
                ProcessMediaFile::dispatch($item, null, $item->temp_file_path);
                $redispatched++;
            } elseif ($item->source_url) {
                ProcessMediaFile::dispatch($item, $item->source_url, null);
                $redispatched++;
            } else {
                $item->update([
                    'processing_status' => ProcessingStatusType::FAILED,
                    'processing_completed_at' => now(),
                    'processing_error' => 'Processing job was lost and no recovery path available (no temp file or source URL).',
                ]);
                $failed++;
            }
        }

        if ($redispatched > 0) {
            $this->info("Re-dispatched {$redispatched} pending item(s).");
        }

        if ($failed > 0) {
            $this->warn("Marked {$failed} item(s) as failed (no recovery path).");
        }

        return Command::SUCCESS;
    }
}
