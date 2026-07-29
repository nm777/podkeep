<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class PruneFailedJobs extends Command
{
    protected $signature = 'queue:prune-failed';

    protected $description = 'Delete failed_jobs entries older than the retention window.';

    public function handle(): int
    {
        $days = (int) config('services.admin.failed_retention_days', 30);

        $deleted = DB::table('failed_jobs')
            ->where('failed_at', '<', now()->subDays($days))
            ->delete();

        $this->info("Pruned {$deleted} failed jobs older than {$days} days.");

        return self::SUCCESS;
    }
}
