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

test('shared feeds prop passes is_hidden_from_selector through for both states', function () {
    // The add-media picker filters hidden feeds client-side on this prop value,
    // so the backend must serialize is_hidden_from_selector for every feed.
    $user = User::factory()->create();

    Feed::factory()->create(['user_id' => $user->id, 'title' => 'Hidden From Picker', 'is_hidden_from_selector' => true]);
    Feed::factory()->create(['user_id' => $user->id, 'title' => 'Visible In Picker', 'is_hidden_from_selector' => false]);

    $feeds = $this->actingAs($user)->get('/feeds')->inertiaProps('feeds');
    $byTitle = array_column((array) $feeds, 'is_hidden_from_selector', 'title');

    expect($byTitle)
        ->toHaveCount(2)
        ->and($byTitle['Hidden From Picker'])->toBe(true)
        ->and($byTitle['Visible In Picker'])->toBe(false);
});
