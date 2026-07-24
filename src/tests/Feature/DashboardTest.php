<?php

use App\Models\Feed;
use App\Models\User;

test('guests are redirected to the login page', function () {
    $this->get('/feeds')->assertRedirect('/login');
});

test('authenticated users can visit the dashboard', function () {
    $this->actingAs($user = User::factory()->create());

    $this->get('/feeds')->assertOk();
});

test('hidden feeds still appear in the shared feeds prop', function () {
    $user = User::factory()->create();

    Feed::factory()->create([
        'user_id' => $user->id,
        'title' => 'Hidden From Picker',
        'is_hidden_from_selector' => true,
    ]);

    $response = $this->actingAs($user)->get('/feeds');

    $response->assertStatus(200);
    $response->assertInertia(
        fn ($page) => $page
            ->has('feeds', 1)
            ->where('feeds.0.title', 'Hidden From Picker')
            ->where('feeds.0.is_hidden_from_selector', true)
    );
});
