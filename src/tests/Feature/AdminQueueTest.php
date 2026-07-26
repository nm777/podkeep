<?php

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
