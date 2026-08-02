<?php

use App\Jobs\SegmentTranscriptIntoChapters;
use App\Models\LibraryItem;
use App\Models\MediaFile;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;

uses(RefreshDatabase::class);

it('allows an admin to view the queue page', function () {
    $admin = User::factory()->admin()->create();

    DB::table('jobs')->insert([
        'queue' => 'default',
        'payload' => json_encode(['displayName' => 'App\\Jobs\\TestJob', 'data' => []]),
        'attempts' => 0,
        'reserved_at' => null,
        'available_at' => now()->addSeconds(60)->timestamp,
        'created_at' => now()->timestamp,
    ]);

    DB::table('failed_jobs')->insert([
        'uuid' => 'test-uuid-123',
        'connection' => 'database',
        'queue' => 'chapters',
        'payload' => json_encode(['displayName' => 'App\\Jobs\\FailedJob']),
        'exception' => 'Test failure message',
    ]);

    $this->actingAs($admin)
        ->get('/admin/queue')
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('admin/queue/index')
            ->has('pending')
            ->has('executing')
            ->has('failed')
        );
});

it('forbids non-admin users from viewing the queue page', function () {
    $user = User::factory()->create();

    $this->actingAs($user)->get('/admin/queue')->assertForbidden();
});

it('redirects unauthenticated visitors to the login page', function () {
    $this->get('/admin/queue')->assertRedirect(route('login'));
});

it('exposes the job type for each pending job', function () {
    $admin = User::factory()->admin()->create();

    DB::table('jobs')->insert([
        'queue' => 'default',
        'payload' => json_encode(['displayName' => 'App\\Jobs\\TestJob', 'data' => []]),
        'attempts' => 0,
        'reserved_at' => null,
        'available_at' => now()->addSeconds(60)->timestamp,
        'created_at' => now()->timestamp,
    ]);

    $this->actingAs($admin)
        ->get('/admin/queue')
        ->assertInertia(fn ($page) => $page->where('pending.0.type', 'App\\Jobs\\TestJob'));
});

it('does not expose the raw job payload', function () {
    $admin = User::factory()->admin()->create();

    DB::table('jobs')->insert([
        'queue' => 'default',
        'payload' => json_encode(['displayName' => 'App\\Jobs\\TestJob', 'data' => ['secret' => 'hidden']]),
        'attempts' => 0,
        'reserved_at' => null,
        'available_at' => now()->addSeconds(60)->timestamp,
        'created_at' => now()->timestamp,
    ]);

    $this->actingAs($admin)
        ->get('/admin/queue')
        ->assertInertia(fn ($page) => $page->missing('pending.0.payload'));
});

it('identifies the media file for a queued job', function () {
    $admin = User::factory()->admin()->create();
    $mediaFile = MediaFile::factory()->create();
    $libraryItem = LibraryItem::factory()->create([
        'media_file_id' => $mediaFile->id,
        'title' => "God's Smuggler",
    ]);

    DB::table('jobs')->insert([
        'queue' => 'chapters',
        'payload' => json_encode([
            'displayName' => SegmentTranscriptIntoChapters::class,
            'data' => ['command' => serialize(new SegmentTranscriptIntoChapters($mediaFile))],
        ]),
        'attempts' => 0,
        'reserved_at' => null,
        'available_at' => now()->addSeconds(60)->timestamp,
        'created_at' => now()->timestamp,
    ]);

    $this->actingAs($admin)
        ->get('/admin/queue')
        ->assertInertia(fn ($page) => $page
            ->where('pending.0.media.id', $mediaFile->id)
            ->where('pending.0.media.title', $libraryItem->title)
            ->where('pending.0.media.url', route('files.show', ['file_path' => $mediaFile->file_path]))
        );
});

it('allows an admin to cancel a pending job', function () {
    $admin = User::factory()->admin()->create();

    $jobId = DB::table('jobs')->insertGetId([
        'queue' => 'default',
        'payload' => json_encode(['displayName' => 'App\\Jobs\\TestJob', 'data' => []]),
        'attempts' => 0,
        'reserved_at' => null,
        'available_at' => now()->addSeconds(60)->timestamp,
        'created_at' => now()->timestamp,
    ]);

    $this->actingAs($admin)
        ->post("/admin/queue/{$jobId}/cancel")
        ->assertRedirect();

    $this->assertDatabaseMissing('jobs', ['id' => $jobId]);
});

it('does not release an executing job', function () {
    $admin = User::factory()->admin()->create();
    $reservedAt = now()->timestamp;

    $jobId = DB::table('jobs')->insertGetId([
        'queue' => 'default',
        'payload' => json_encode(['displayName' => 'App\\Jobs\\TestJob', 'data' => []]),
        'attempts' => 1,
        'reserved_at' => $reservedAt,
        'available_at' => now()->subSeconds(60)->timestamp,
        'created_at' => now()->subSeconds(120)->timestamp,
    ]);

    $this->actingAs($admin)
        ->post("/admin/queue/{$jobId}/release")
        ->assertRedirect()
        ->assertSessionHas('warning', 'Executing jobs cannot be released.');

    $this->assertDatabaseHas('jobs', ['id' => $jobId, 'reserved_at' => $reservedAt]);
});

it('allows an admin to retry a failed job', function () {
    $admin = User::factory()->admin()->create();

    DB::table('failed_jobs')->insert([
        'uuid' => 'retry-test-uuid',
        'connection' => 'database',
        'queue' => 'chapters',
        'payload' => json_encode(['displayName' => 'App\\Jobs\\FailedJob', 'data' => []]),
        'exception' => 'Test failure message',
        'failed_at' => now(),
    ]);

    $this->actingAs($admin)
        ->post('/admin/queue/failed/retry-test-uuid/retry')
        ->assertRedirect();

    $this->assertDatabaseHas('jobs', ['queue' => 'chapters']);
});

it('allows an admin to delete a failed job', function () {
    $admin = User::factory()->admin()->create();

    DB::table('failed_jobs')->insert([
        'uuid' => 'delete-test-uuid',
        'connection' => 'database',
        'queue' => 'chapters',
        'payload' => json_encode(['displayName' => 'App\\Jobs\\FailedJob', 'data' => []]),
        'exception' => 'Test failure message',
        'failed_at' => now(),
    ]);

    $this->actingAs($admin)
        ->post('/admin/queue/failed/delete-test-uuid/delete')
        ->assertRedirect();

    $this->assertDatabaseMissing('failed_jobs', ['uuid' => 'delete-test-uuid']);
});

it('forbids non-admin users from cancelling a job', function () {
    $user = User::factory()->create();

    $jobId = DB::table('jobs')->insertGetId([
        'queue' => 'default',
        'payload' => json_encode(['displayName' => 'App\\Jobs\\TestJob', 'data' => []]),
        'attempts' => 0,
        'reserved_at' => null,
        'available_at' => now()->addSeconds(60)->timestamp,
        'created_at' => now()->timestamp,
    ]);

    $this->actingAs($user)
        ->post("/admin/queue/{$jobId}/cancel")
        ->assertForbidden();
});
