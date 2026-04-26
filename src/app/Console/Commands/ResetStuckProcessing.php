<?php

namespace App\Console\Commands;

use App\Models\LibraryItem;
use Illuminate\Console\Command;

class ResetStuckProcessing extends Command
{
    protected $signature = 'media:reset-stuck 
                            {--hours=1 : Consider items stuck if processing for longer than this many hours}
                            {--force : Reset items without confirmation}';

    protected $description = 'Reset library items that have been stuck in processing status';

    public function handle()
    {
        $hours = (int) $this->option('hours');
        $cutoff = now()->subHours($hours);

        $stuckItems = LibraryItem::where('processing_status', 'processing')
            ->where('processing_started_at', '<', $cutoff)
            ->get();

        if ($stuckItems->isEmpty()) {
            $this->info('No stuck items found.');

            return Command::SUCCESS;
        }

        $this->warn("Found {$stuckItems->count()} item(s) stuck in processing status for more than {$hours} hour(s):");
        $this->newLine();

        foreach ($stuckItems as $item) {
            $duration = $item->processing_started_at->diffForHumans();
            $this->line("  - ID {$item->id}: {$item->title} (started {$duration})");
        }

        $this->newLine();

        if (! $this->option('force') && ! $this->confirm('Reset these items to failed status?')) {
            $this->info('Cancelled.');

            return Command::SUCCESS;
        }

        $count = 0;
        foreach ($stuckItems as $item) {
            $item->update([
                'processing_status' => 'failed',
                'processing_completed_at' => now(),
                'processing_error' => 'Processing was interrupted. Please try redownloading.',
            ]);
            $count++;
        }

        $this->info("Reset {$count} item(s) to failed status.");

        return Command::SUCCESS;
    }
}
