<?php

use App\Models\User;

it('renders the appearance settings page for authenticated users', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->get('/settings/appearance');

    $response->assertOk();
});

it('redirects unauthenticated users to login', function () {
    $response = $this->get('/settings/appearance');

    $response->assertRedirect(route('login'));
});
