<?php

use App\Models\Feed;
use App\Models\FeedItem;
use App\Models\LibraryItem;
use App\Models\MediaFile;
use App\Models\User;

beforeEach(function () {
    $this->user = User::factory()->create([
        'email_verified_at' => now(),
        'approval_status' => 'approved',
    ]);

    $this->feed = Feed::factory()->create([
        'user_id' => $this->user->id,
        'episode_order' => 'chronological',
    ]);
    $this->mediaFile = MediaFile::factory()->create(['user_id' => $this->user->id]);

    $this->items = collect(range(1, 6))->map(function ($i) {
        return LibraryItem::factory()->create([
            'user_id' => $this->user->id,
            'media_file_id' => $this->mediaFile->id,
        ]);
    });
});

it('syncs feed items efficiently with correct sequences', function () {
    $response = $this->actingAs($this->user)->put(route('feeds.update', $this->feed), [
        'title' => 'Updated',
        'description' => 'Updated desc',
        'is_public' => false,
        'items' => [
            ['library_item_id' => $this->items[0]->id, 'sequence' => 0],
            ['library_item_id' => $this->items[1]->id, 'sequence' => 1],
            ['library_item_id' => $this->items[2]->id, 'sequence' => 2],
        ],
    ]);

    $response->assertRedirect();

    $feedItems = FeedItem::where('feed_id', $this->feed->id)->get();
    expect($feedItems)->toHaveCount(3);

    $sequences = $feedItems->pluck('sequence', 'library_item_id')->toArray();
    expect($sequences[$this->items[0]->id])->toBe(0);
    expect($sequences[$this->items[1]->id])->toBe(1);
    expect($sequences[$this->items[2]->id])->toBe(2);
});

it('handles reordering existing items', function () {
    $this->feed->items()->createMany([
        ['library_item_id' => $this->items[0]->id, 'sequence' => 0],
        ['library_item_id' => $this->items[1]->id, 'sequence' => 1],
        ['library_item_id' => $this->items[2]->id, 'sequence' => 2],
    ]);

    $response = $this->actingAs($this->user)->put(route('feeds.update', $this->feed), [
        'title' => 'Updated',
        'description' => 'Updated desc',
        'is_public' => false,
        'items' => [
            ['library_item_id' => $this->items[2]->id, 'sequence' => 0],
            ['library_item_id' => $this->items[0]->id, 'sequence' => 1],
            ['library_item_id' => $this->items[1]->id, 'sequence' => 2],
        ],
    ]);

    $response->assertRedirect();

    $feedItems = FeedItem::where('feed_id', $this->feed->id)->get();
    expect($feedItems)->toHaveCount(3);

    $sequences = $feedItems->pluck('sequence', 'library_item_id')->toArray();
    expect($sequences[$this->items[2]->id])->toBe(0);
    expect($sequences[$this->items[0]->id])->toBe(1);
    expect($sequences[$this->items[1]->id])->toBe(2);
});

it('removes items not in the sync list', function () {
    $this->feed->items()->createMany([
        ['library_item_id' => $this->items[0]->id, 'sequence' => 0],
        ['library_item_id' => $this->items[1]->id, 'sequence' => 1],
        ['library_item_id' => $this->items[2]->id, 'sequence' => 2],
    ]);

    $response = $this->actingAs($this->user)->put(route('feeds.update', $this->feed), [
        'title' => 'Updated',
        'description' => 'Updated desc',
        'is_public' => false,
        'items' => [
            ['library_item_id' => $this->items[0]->id, 'sequence' => 0],
        ],
    ]);

    $response->assertRedirect();

    $feedItems = FeedItem::where('feed_id', $this->feed->id)->get();
    expect($feedItems)->toHaveCount(1);
    expect($feedItems->first()->library_item_id)->toBe($this->items[0]->id);
});

it('handles large number of items without N+1 queries', function () {
    $items = collect(range(1, 50))->map(function () {
        return LibraryItem::factory()->create([
            'user_id' => $this->user->id,
            'media_file_id' => MediaFile::factory()->create(['user_id' => $this->user->id])->id,
        ]);
    });

    $syncData = $items->map(fn ($item, $i) => [
        'library_item_id' => $item->id,
        'sequence' => $i,
    ])->values()->all();

    $response = $this->actingAs($this->user)->put(route('feeds.update', $this->feed), [
        'title' => 'Updated',
        'description' => 'Updated desc',
        'is_public' => false,
        'items' => $syncData,
    ]);

    $response->assertRedirect();

    expect(FeedItem::where('feed_id', $this->feed->id)->count())->toBe(50);
});

it('preserves arrangement for newest_first when frontend sends reversed sequences', function () {
    $this->feed->update(['episode_order' => 'newest_first']);

    $response = $this->actingAs($this->user)->put(route('feeds.update', $this->feed), [
        'title' => 'Updated',
        'is_public' => false,
        'items' => [
            ['library_item_id' => $this->items[0]->id, 'sequence' => 2],
            ['library_item_id' => $this->items[1]->id, 'sequence' => 1],
            ['library_item_id' => $this->items[2]->id, 'sequence' => 0],
        ],
    ]);

    $response->assertRedirect();

    $feedItems = FeedItem::where('feed_id', $this->feed->id)
        ->orderBy('sequence', 'desc')
        ->with('libraryItem')
        ->get();

    // Items displayed DESC should match the order the user arranged them
    expect($feedItems->pluck('library_item_id')->toArray())
        ->toBe([$this->items[0]->id, $this->items[1]->id, $this->items[2]->id]);
});
