<?php

use App\Models\Feed;
use App\Models\LibraryItem;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

describe('FeedController syncFeedItems', function () {
    it('clears all feed items when synced with empty array', function () {
        $user = User::factory()->create();
        $feed = Feed::factory()->create(['user_id' => $user->id]);
        $items = LibraryItem::factory()->count(3)->create([
            'user_id' => $user->id,
            'processing_status' => 'completed',
        ]);

        foreach ($items as $item) {
            $feed->items()->create([
                'library_item_id' => $item->id,
                'sequence' => 0,
            ]);
        }

        expect($feed->fresh()->items)->toHaveCount(3);

        $response = $this->actingAs($user)->put("/feeds/{$feed->id}", [
            'title' => $feed->title,
            'items' => [],
        ]);

        $response->assertRedirect();
        expect($feed->fresh()->items)->toHaveCount(0);
    });
});
