<?php

namespace App\Listeners;

use Illuminate\Queue\Events\JobProcessed;
use Illuminate\Support\Facades\DB;

class LogCompletedJob
{
    public function handle(JobProcessed $event): void
    {
        DB::table('completed_job_log')->insert([
            'job_type' => $event->job->resolveName(),
            'queue' => $event->job->getQueue(),
            'completed_at' => now(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
