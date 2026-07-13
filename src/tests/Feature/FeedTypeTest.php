<?php

use App\Enums\FeedType;
use App\Models\Feed;
use App\Models\FeedItem;
use App\Models\LibraryItem;
use App\Models\MediaFile;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->user = User::factory()->create([
        'email_verified_at' => now(),
        'approval_status' => 'approved',
    ]);
    Cache::flush();
});

// === US1: Feed type selection ===

it('creates a feed with static feed type', function () {
    $response = $this->actingAs($this->user)->post('/feeds', [
        'title' => 'My Audiobook',
        'feed_type' => 'static',
    ]);

    $response->assertRedirect();
    $this->assertDatabaseHas('feeds', [
        'user_id' => $this->user->id,
        'title' => 'My Audiobook',
        'feed_type' => 'static',
    ]);
});

it('creates a feed with append feed type', function () {
    $response = $this->actingAs($this->user)->post('/feeds', [
        'title' => 'My Podcast',
        'feed_type' => 'append',
    ]);

    $response->assertRedirect();
    $this->assertDatabaseHas('feeds', [
        'user_id' => $this->user->id,
        'title' => 'My Podcast',
        'feed_type' => 'append',
    ]);
});

it('defaults to append feed type when not specified', function () {
    $response = $this->actingAs($this->user)->post('/feeds', [
        'title' => 'Default Feed',
    ]);

    $response->assertRedirect();
    $this->assertDatabaseHas('feeds', [
        'title' => 'Default Feed',
        'feed_type' => 'append',
    ]);
});

it('updates feed type from static to append', function () {
    $feed = Feed::factory()->create([
        'user_id' => $this->user->id,
        'feed_type' => 'static',
    ]);

    $response = $this->actingAs($this->user)->put("/feeds/{$feed->id}", [
        'title' => $feed->title,
        'feed_type' => 'append',
    ]);

    $response->assertRedirect();
    expect($feed->fresh()->feed_type)->toBe(FeedType::Append);
});

it('updates feed type from append to static', function () {
    $feed = Feed::factory()->create([
        'user_id' => $this->user->id,
        'feed_type' => 'append',
    ]);

    $response = $this->actingAs($this->user)->put("/feeds/{$feed->id}", [
        'title' => $feed->title,
        'feed_type' => 'static',
    ]);

    $response->assertRedirect();
    expect($feed->fresh()->feed_type)->toBe(FeedType::Static);
});

it('loads static feed items in sequence order on edit page', function () {
    $feed = Feed::factory()->create([
        'user_id' => $this->user->id,
        'feed_type' => 'static',
    ]);
    $mediaFile = MediaFile::factory()->create(['user_id' => $this->user->id]);

    foreach ([2, 0, 1] as $sequence) {
        $item = LibraryItem::factory()->create([
            'user_id' => $this->user->id,
            'media_file_id' => $mediaFile->id,
        ]);

        $feed->items()->create([
            'library_item_id' => $item->id,
            'sequence' => $sequence,
        ]);
    }

    $response = $this->actingAs($this->user)->get("/feeds/{$feed->id}/edit");

    $response->assertOk()
        ->assertInertia(fn ($page) => $page
            ->has('feed.items', 3)
            ->where('feed.items.0.sequence', 0)
            ->where('feed.items.1.sequence', 1)
            ->where('feed.items.2.sequence', 2)
        );
});

it('loads append feed items newest-first on edit page', function () {
    $feed = Feed::factory()->create([
        'user_id' => $this->user->id,
        'feed_type' => 'append',
    ]);
    $mediaFile = MediaFile::factory()->create(['user_id' => $this->user->id]);

    $old = LibraryItem::factory()->create(['user_id' => $this->user->id, 'media_file_id' => $mediaFile->id]);
    $new = LibraryItem::factory()->create(['user_id' => $this->user->id, 'media_file_id' => $mediaFile->id]);

    $oldItem = $feed->items()->create(['library_item_id' => $old->id, 'sequence' => 0]);
    $newItem = $feed->items()->create(['library_item_id' => $new->id, 'sequence' => 1]);

    $oldItem->forceFill(['created_at' => now()->subHour()])->save();
    $newItem->forceFill(['created_at' => now()])->save();

    $response = $this->actingAs($this->user)->get("/feeds/{$feed->id}/edit");

    $response->assertOk()
        ->assertInertia(fn ($page) => $page
            ->has('feed.items', 2)
            ->where('feed.items.0.library_item_id', $new->id)
            ->where('feed.items.1.library_item_id', $old->id)
        );
});

// === US2: Static feed RSS pubDate tests ===

it('generates sequence-derived pubDates for static feeds', function () {
    $feed = Feed::factory()->create([
        'user_id' => $this->user->id,
        'feed_type' => 'static',
        'is_public' => true,
    ]);
    $mediaFile = MediaFile::factory()->create(['user_id' => $this->user->id]);

    foreach ([0, 1, 2] as $seq) {
        $item = LibraryItem::factory()->create([
            'user_id' => $this->user->id,
            'media_file_id' => $mediaFile->id,
        ]);
        $feed->items()->create([
            'library_item_id' => $item->id,
            'sequence' => $seq,
        ]);
    }

    $response = $this->get("/rss/{$feed->user_guid}/{$feed->slug}");

    $response->assertOk();
    $content = $response->content();

    // Verify pubDates are feed.created_at + 0, 1, 2 minutes
    foreach ([0, 1, 2] as $min) {
        $expectedDate = $feed->created_at->copy()->addMinutes($min)->toRfc822String();
        expect($content)->toContain($expectedDate);
    }
});

it('orders static feed RSS items by sequence ascending', function () {
    $feed = Feed::factory()->create([
        'user_id' => $this->user->id,
        'feed_type' => 'static',
        'is_public' => true,
    ]);
    $mediaFile = MediaFile::factory()->create(['user_id' => $this->user->id]);

    $titles = ['C Chapter', 'A Chapter', 'B Chapter'];
    foreach ([2, 0, 1] as $i => $seq) {
        $item = LibraryItem::factory()->create([
            'user_id' => $this->user->id,
            'media_file_id' => $mediaFile->id,
            'title' => $titles[$i],
        ]);
        $feed->items()->create([
            'library_item_id' => $item->id,
            'sequence' => $seq,
        ]);
    }

    $response = $this->get("/rss/{$feed->user_guid}/{$feed->slug}");

    $content = $response->content();
    $posA = strpos($content, 'A Chapter');
    $posB = strpos($content, 'B Chapter');
    $posC = strpos($content, 'C Chapter');

    expect($posA)->toBeLessThan($posB);
    expect($posB)->toBeLessThan($posC);
});

// === US3: Append feed RSS tests ===

it('orders append feed RSS items newest-first by created_at', function () {
    $feed = Feed::factory()->create([
        'user_id' => $this->user->id,
        'feed_type' => 'append',
        'is_public' => true,
    ]);
    $mediaFile = MediaFile::factory()->create(['user_id' => $this->user->id]);

    $old = LibraryItem::factory()->create(['user_id' => $this->user->id, 'media_file_id' => $mediaFile->id, 'title' => 'Old Episode']);
    $new = LibraryItem::factory()->create(['user_id' => $this->user->id, 'media_file_id' => $mediaFile->id, 'title' => 'New Episode']);

    $oldFeedItem = $feed->items()->create(['library_item_id' => $old->id, 'sequence' => 0]);
    $newFeedItem = $feed->items()->create(['library_item_id' => $new->id, 'sequence' => 1]);

    DB::table('feed_items')->where('id', $oldFeedItem->id)->update(['created_at' => now()->subHour()]);
    DB::table('feed_items')->where('id', $newFeedItem->id)->update(['created_at' => now()]);

    $response = $this->get("/rss/{$feed->user_guid}/{$feed->slug}");

    $content = $response->content();
    $posNew = strpos($content, 'New Episode');
    $posOld = strpos($content, 'Old Episode');

    expect($posNew)->toBeLessThan($posOld);
});

it('shows display date prefix in RSS description for append feeds', function () {
    $feed = Feed::factory()->create([
        'user_id' => $this->user->id,
        'feed_type' => 'append',
        'is_public' => true,
    ]);
    $mediaFile = MediaFile::factory()->create(['user_id' => $this->user->id]);

    $item = LibraryItem::factory()->create([
        'user_id' => $this->user->id,
        'media_file_id' => $mediaFile->id,
        'title' => 'Test Episode',
        'description' => 'A great episode',
        'display_date' => '2026-07-04',
    ]);

    $feed->items()->create([
        'library_item_id' => $item->id,
        'sequence' => 0,
    ]);

    $response = $this->get("/rss/{$feed->user_guid}/{$feed->slug}");

    $response->assertOk();
    expect($response->content())->toContain('[Jul 4, 2026]');
    expect($response->content())->toContain('A great episode');
});

it('reorders items by created_at desc when switching to append type', function () {
    $feed = Feed::factory()->create([
        'user_id' => $this->user->id,
        'feed_type' => 'static',
    ]);
    $mediaFile = MediaFile::factory()->create(['user_id' => $this->user->id]);

    $item1 = LibraryItem::factory()->create(['user_id' => $this->user->id, 'media_file_id' => $mediaFile->id]);
    $item2 = LibraryItem::factory()->create(['user_id' => $this->user->id, 'media_file_id' => $mediaFile->id]);

    $fi1 = $feed->items()->create(['library_item_id' => $item1->id, 'sequence' => 0]);
    $fi2 = $feed->items()->create(['library_item_id' => $item2->id, 'sequence' => 1]);

    // item2 was added later (newer created_at)
    DB::table('feed_items')->where('id', $fi1->id)->update(['created_at' => now()->subHour()]);
    DB::table('feed_items')->where('id', $fi2->id)->update(['created_at' => now()]);

    // Switch to append
    $response = $this->actingAs($this->user)->put("/feeds/{$feed->id}", [
        'title' => $feed->title,
        'feed_type' => 'append',
    ]);

    $response->assertRedirect();

    // Verify feed type actually changed
    expect($feed->fresh()->feed_type)->toBe(FeedType::Append);

    // Reload from DB
    $fi1Fresh = FeedItem::find($fi1->id);
    $fi2Fresh = FeedItem::find($fi2->id);

    // item2 (added later) should have lower sequence (appears first in DESC)
    expect($fi2Fresh->sequence)->toBeLessThan($fi1Fresh->sequence);
});
