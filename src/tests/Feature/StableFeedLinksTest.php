<?php

use App\Models\Feed;
use App\Models\User;

it('keeps the slug unchanged when renaming a feed via the web update', function () {
    $user = User::factory()->create();
    $feed = Feed::factory()->create([
        'user_id' => $user->id,
        'title' => 'Original Title',
        'slug' => 'original-slug',
        'user_guid' => 'original-guid',
        'is_public' => true,
    ]);

    $response = $this->actingAs($user)->put("/feeds/{$feed->id}", [
        'title' => 'Brand New Title',
    ]);

    $response->assertRedirect("/feeds/{$feed->id}/edit");
    $this->assertDatabaseHas('feeds', [
        'id' => $feed->id,
        'title' => 'Brand New Title',
        'slug' => 'original-slug',
    ]);
});

it('keeps the original RSS URL resolving after a rename and shows the new title', function () {
    $user = User::factory()->create();
    $feed = Feed::factory()->create([
        'user_id' => $user->id,
        'title' => 'Old Title',
        'slug' => 'original-slug',
        'user_guid' => 'original-guid',
        'is_public' => true,
    ]);

    $this->actingAs($user)->put("/feeds/{$feed->id}", [
        'title' => 'Shiny New Title',
        'is_public' => true,
    ]);

    $response = $this->get('/rss/original-guid/original-slug');

    $response->assertOk();
    expect($response->getContent())->toContain('Shiny New Title');
});

it('keeps the original share URL resolving after a rename', function () {
    $user = User::factory()->create();
    $feed = Feed::factory()->create([
        'user_id' => $user->id,
        'title' => 'Old Title',
        'slug' => 'original-slug',
        'user_guid' => 'original-guid',
        'is_public' => true,
    ]);

    $this->actingAs($user)->put("/feeds/{$feed->id}", [
        'title' => 'Shiny New Title',
        'is_public' => true,
    ]);

    $response = $this->get('/share/original-guid/original-slug');

    $response->assertOk();
    $response->assertInertia(fn ($page) => $page
        ->component('share/show')
        ->where('feed.title', 'Shiny New Title')
    );
});

it('keeps the slug unchanged across multiple sequential renames', function () {
    $user = User::factory()->create();
    $feed = Feed::factory()->create([
        'user_id' => $user->id,
        'title' => 'Original Title',
        'slug' => 'original-slug',
        'user_guid' => 'original-guid',
        'is_public' => true,
    ]);

    $this->actingAs($user)->put("/feeds/{$feed->id}", [
        'title' => 'Second Title',
        'is_public' => true,
    ]);

    $this->actingAs($user)->put("/feeds/{$feed->id}", [
        'title' => 'Third Title',
        'is_public' => true,
    ]);

    $this->assertDatabaseHas('feeds', [
        'id' => $feed->id,
        'slug' => 'original-slug',
    ]);

    $response = $this->get('/rss/original-guid/original-slug');

    $response->assertOk();
    expect($response->getContent())->toContain('Third Title');
});

it('keeps the slug unchanged when editing only the description', function () {
    $user = User::factory()->create();
    $feed = Feed::factory()->create([
        'user_id' => $user->id,
        'title' => 'Original Title',
        'slug' => 'original-slug',
        'user_guid' => 'original-guid',
        'is_public' => true,
    ]);

    $response = $this->actingAs($user)->put("/feeds/{$feed->id}", [
        'title' => 'Original Title',
        'description' => 'A brand new description',
    ]);

    $response->assertRedirect("/feeds/{$feed->id}/edit");
    $this->assertDatabaseHas('feeds', [
        'id' => $feed->id,
        'slug' => 'original-slug',
        'description' => 'A brand new description',
    ]);
});

it('keeps the slug unchanged when toggling is_public', function () {
    $user = User::factory()->create();
    $feed = Feed::factory()->create([
        'user_id' => $user->id,
        'title' => 'Original Title',
        'slug' => 'original-slug',
        'user_guid' => 'original-guid',
        'is_public' => true,
    ]);

    $response = $this->actingAs($user)->put("/feeds/{$feed->id}", [
        'title' => 'Original Title',
        'is_public' => false,
    ]);

    $response->assertRedirect("/feeds/{$feed->id}/edit");
    $this->assertDatabaseHas('feeds', [
        'id' => $feed->id,
        'slug' => 'original-slug',
        'is_public' => false,
    ]);
});

it('keeps the slug unchanged when renaming via the API', function () {
    $user = User::factory()->create();
    $token = $user->createToken('test')->plainTextToken;
    $feed = Feed::factory()->create([
        'user_id' => $user->id,
        'title' => 'Original Title',
        'slug' => 'original-slug',
        'user_guid' => 'original-guid',
        'is_public' => true,
    ]);

    $response = $this->withHeader('Authorization', 'Bearer '.$token)
        ->putJson('/api/v1/feeds/'.$feed->id, [
            'title' => 'API Renamed',
        ]);

    $response->assertOk();
    $response->assertJsonPath('data.title', 'API Renamed');
    $response->assertJsonPath('data.slug', 'original-slug');
    $response->assertJsonPath('data.user_guid', 'original-guid');
    $this->assertDatabaseHas('feeds', [
        'id' => $feed->id,
        'slug' => 'original-slug',
    ]);
});

it('shows the latest title after renames via both web and API', function () {
    $user = User::factory()->create();
    $token = $user->createToken('test')->plainTextToken;
    $feed = Feed::factory()->create([
        'user_id' => $user->id,
        'title' => 'Original Title',
        'slug' => 'original-slug',
        'user_guid' => 'original-guid',
        'is_public' => true,
    ]);

    $this->actingAs($user)->put("/feeds/{$feed->id}", [
        'title' => 'Web Name',
        'is_public' => true,
    ]);

    $this->withHeader('Authorization', 'Bearer '.$token)
        ->putJson('/api/v1/feeds/'.$feed->id, [
            'title' => 'API Name',
        ]);

    $response = $this->get('/share/original-guid/original-slug');

    $response->assertOk();
    $response->assertInertia(fn ($page) => $page
        ->where('feed.title', 'API Name')
    );
});

it('still prevents non-owners from renaming a feed', function () {
    $userA = User::factory()->create();
    $userB = User::factory()->create();
    $feed = Feed::factory()->create([
        'user_id' => $userA->id,
        'title' => 'Original Title',
        'slug' => 'original-slug',
        'user_guid' => 'original-guid',
        'is_public' => true,
    ]);

    $response = $this->actingAs($userB)->put("/feeds/{$feed->id}", [
        'title' => 'Hijacked Title',
    ]);

    $response->assertForbidden();
    $this->assertDatabaseHas('feeds', [
        'id' => $feed->id,
        'title' => 'Original Title',
        'slug' => 'original-slug',
    ]);
});
