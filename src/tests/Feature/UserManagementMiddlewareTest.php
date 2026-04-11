<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

describe('UserManagement middleware', function () {
    it('requires authentication', function () {
        $this->get('/admin/users')->assertRedirect(route('login'));
    });

    it('requires admin role', function () {
        $user = User::factory()->create(['approval_status' => 'approved', 'is_admin' => false]);

        $this->actingAs($user)->get('/admin/users')->assertForbidden();
    });

    it('allows admin access', function () {
        $admin = User::factory()->create(['approval_status' => 'approved', 'is_admin' => true]);

        $this->actingAs($admin)->get('/admin/users')->assertSuccessful();
    });
});
