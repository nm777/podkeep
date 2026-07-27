<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class PruneCompletedJobs extends Command
{
    protected $signature = 'queue:prune-completed';
    protected $description = 'Delete completed_job_log entries older than the retention window.';

    public function handle(): int
    {
        $days = (int) config('admin.completed_retention_days', 3);
        $deleted = DB::table('completed_job_log')
            ->where('completed_at', '<', now()->subDays($days))
            ->delete();

        $this->info("Pruned {$deleted} completed job log entries older than {$days} days.");

        return self::SUCCESS;
    }
}
