<?php

use Illuminate\Support\Facades\DB;

it('prunes completed jobs using the configured retention days', function () {
    config()->set('services.admin.completed_retention_days', 7);

    DB::table('completed_job_log')->insert([
        ['job_type' => 'OldJob', 'queue' => 'default', 'completed_at' => now()->subDays(8), 'created_at' => now(), 'updated_at' => now()],
        ['job_type' => 'RecentJob', 'queue' => 'default', 'completed_at' => now()->subDays(6), 'created_at' => now(), 'updated_at' => now()],
    ]);

    $this->artisan('queue:prune-completed')->assertSuccessful();

    $this->assertDatabaseMissing('completed_job_log', ['job_type' => 'OldJob']);
    $this->assertDatabaseHas('completed_job_log', ['job_type' => 'RecentJob']);
});

it('prunes failed jobs using the configured retention days', function () {
    config()->set('services.admin.failed_retention_days', 14);

    DB::table('failed_jobs')->insert([
        ['uuid' => 'old-job', 'connection' => 'database', 'queue' => 'default', 'payload' => '{}', 'exception' => 'Failed', 'failed_at' => now()->subDays(15)],
        ['uuid' => 'recent-job', 'connection' => 'database', 'queue' => 'default', 'payload' => '{}', 'exception' => 'Failed', 'failed_at' => now()->subDays(13)],
    ]);

    $this->artisan('queue:prune-failed')->assertSuccessful();

    $this->assertDatabaseMissing('failed_jobs', ['uuid' => 'old-job']);
    $this->assertDatabaseHas('failed_jobs', ['uuid' => 'recent-job']);
});
