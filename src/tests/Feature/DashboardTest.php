<?php

use App\Models\Feed;
use App\Models\LibraryItem;
use App\Models\MediaFile;
use App\Models\User;

test('guests are redirected to the login page', function () {
    $this->get('/feeds')->assertRedirect('/login');
});

test('authenticated users can visit the dashboard', function () {
    $this->actingAs($user = User::factory()->create());

    $this->get('/feeds')->assertOk();
});

test('shared auth user contains only fields required by the frontend', function () {
    $user = User::factory()->create([
        'rejection_reason' => 'Internal account note',
    ]);

    $sharedUser = $this->actingAs($user)->get('/feeds')->inertiaProps('auth.user');

    expect(array_keys((array) $sharedUser))->toBe([
        'id',
        'name',
        'email',
        'email_verified_at',
        'is_admin',
    ]);
});

test('dashboard media does not expose file hashes', function () {
    $user = User::factory()->create();
    $mediaFile = MediaFile::factory()->create(['user_id' => $user->id]);
    LibraryItem::factory()->create(['user_id' => $user->id, 'media_file_id' => $mediaFile->id]);

    $libraryItem = $this->actingAs($user)->get('/library')->inertiaProps('libraryItems.0');

    expect((array) $libraryItem['media_file'])->not->toHaveKey('file_hash');
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
