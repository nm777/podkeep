<?php

use App\Models\Feed;
use App\Models\FeedItem;
use App\Models\LibraryItem;
use App\Models\User;

describe('feed item attachment', function () {
    it('attaches a library item to a feed', function () {
        $user = User::factory()->create();
        $token = $user->createToken('test')->plainTextToken;
        $feed = Feed::factory()->create(['user_id' => $user->id]);
        $item = LibraryItem::factory()->create(['user_id' => $user->id]);

        $response = $this->withHeader('Authorization', 'Bearer '.$token)
            ->postJson('/api/v1/feeds/'.$feed->id.'/items', [
                'library_item_id' => $item->id,
            ]);

        $response->assertCreated();
        $response->assertJsonStructure([
            'data' => [
                'id',
                'feed_id',
                'library_item_id',
                'sequence',
            ],
        ]);
        $response->assertJsonPath('data.feed_id', $feed->id);
        $response->assertJsonPath('data.library_item_id', $item->id);

        $this->assertDatabaseHas('feed_items', [
            'feed_id' => $feed->id,
            'library_item_id' => $item->id,
        ]);
    });

    it('prevents attaching to another users feed', function () {
        $userA = User::factory()->create();
        $tokenA = $userA->createToken('test')->plainTextToken;
        $userB = User::factory()->create();
        $feedB = Feed::factory()->create(['user_id' => $userB->id]);
        $itemA = LibraryItem::factory()->create(['user_id' => $userA->id]);

        $response = $this->withHeader('Authorization', 'Bearer '.$tokenA)
            ->postJson('/api/v1/feeds/'.$feedB->id.'/items', [
                'library_item_id' => $itemA->id,
            ]);

        $response->assertNotFound();

        $this->assertDatabaseMissing('feed_items', [
            'feed_id' => $feedB->id,
            'library_item_id' => $itemA->id,
        ]);
    });

    it('prevents attaching another users library item', function () {
        $userA = User::factory()->create();
        $tokenA = $userA->createToken('test')->plainTextToken;
        $userB = User::factory()->create();
        $feedA = Feed::factory()->create(['user_id' => $userA->id]);
        $itemB = LibraryItem::factory()->create(['user_id' => $userB->id]);

        $response = $this->withHeader('Authorization', 'Bearer '.$tokenA)
            ->postJson('/api/v1/feeds/'.$feedA->id.'/items', [
                'library_item_id' => $itemB->id,
            ]);

        expect($response->status())->toBeIn([403, 422]);

        $this->assertDatabaseMissing('feed_items', [
            'feed_id' => $feedA->id,
            'library_item_id' => $itemB->id,
        ]);
    });

    it('defaults sequence to next available', function () {
        $user = User::factory()->create();
        $token = $user->createToken('test')->plainTextToken;
        $feed = Feed::factory()->create(['user_id' => $user->id]);
        $item1 = LibraryItem::factory()->create(['user_id' => $user->id]);
        $item2 = LibraryItem::factory()->create(['user_id' => $user->id]);

        $first = $this->withHeader('Authorization', 'Bearer '.$token)
            ->postJson('/api/v1/feeds/'.$feed->id.'/items', [
                'library_item_id' => $item1->id,
            ]);
        $first->assertCreated();
        $first->assertJsonPath('data.sequence', 0);

        $second = $this->withHeader('Authorization', 'Bearer '.$token)
            ->postJson('/api/v1/feeds/'.$feed->id.'/items', [
                'library_item_id' => $item2->id,
            ]);
        $second->assertCreated();
        $second->assertJsonPath('data.sequence', 1);
    });
});

describe('feed item reordering', function () {
    it('reorders items in a feed', function () {
        $user = User::factory()->create();
        $token = $user->createToken('test')->plainTextToken;
        $feed = Feed::factory()->create(['user_id' => $user->id]);
        $item1 = LibraryItem::factory()->create(['user_id' => $user->id]);
        $item2 = LibraryItem::factory()->create(['user_id' => $user->id]);
        $item3 = LibraryItem::factory()->create(['user_id' => $user->id]);

        $feedItem1 = FeedItem::factory()->create([
            'feed_id' => $feed->id,
            'library_item_id' => $item1->id,
            'sequence' => 0,
        ]);
        $feedItem2 = FeedItem::factory()->create([
            'feed_id' => $feed->id,
            'library_item_id' => $item2->id,
            'sequence' => 1,
        ]);
        $feedItem3 = FeedItem::factory()->create([
            'feed_id' => $feed->id,
            'library_item_id' => $item3->id,
            'sequence' => 2,
        ]);

        $response = $this->withHeader('Authorization', 'Bearer '.$token)
            ->putJson('/api/v1/feeds/'.$feed->id.'/items/reorder', [
                'items' => [
                    ['id' => $feedItem3->id, 'sequence' => 0],
                    ['id' => $feedItem2->id, 'sequence' => 1],
                    ['id' => $feedItem1->id, 'sequence' => 2],
                ],
            ]);

        $response->assertOk();

        expect(FeedItem::find($feedItem3->id)->sequence)->toBe(0);
        expect(FeedItem::find($feedItem2->id)->sequence)->toBe(1);
        expect(FeedItem::find($feedItem1->id)->sequence)->toBe(2);
    });

    it('prevents reordering another users feed', function () {
        $userA = User::factory()->create();
        $tokenA = $userA->createToken('test')->plainTextToken;
        $userB = User::factory()->create();
        $feedB = Feed::factory()->create(['user_id' => $userB->id]);
        $itemB = LibraryItem::factory()->create(['user_id' => $userB->id]);
        $feedItem = FeedItem::factory()->create([
            'feed_id' => $feedB->id,
            'library_item_id' => $itemB->id,
            'sequence' => 0,
        ]);

        $response = $this->withHeader('Authorization', 'Bearer '.$tokenA)
            ->putJson('/api/v1/feeds/'.$feedB->id.'/items/reorder', [
                'items' => [$feedItem->id],
            ]);

        $response->assertNotFound();

        expect(FeedItem::find($feedItem->id)->sequence)->toBe(0);
    });
});

describe('feed item removal', function () {
    it('removes an item from a feed', function () {
        $user = User::factory()->create();
        $token = $user->createToken('test')->plainTextToken;
        $feed = Feed::factory()->create(['user_id' => $user->id]);
        $item = LibraryItem::factory()->create(['user_id' => $user->id]);
        $feedItem = FeedItem::factory()->create([
            'feed_id' => $feed->id,
            'library_item_id' => $item->id,
            'sequence' => 0,
        ]);

        $response = $this->withHeader('Authorization', 'Bearer '.$token)
            ->deleteJson('/api/v1/feeds/'.$feed->id.'/items/'.$feedItem->id);

        $response->assertNoContent();
        $this->assertDatabaseMissing('feed_items', [
            'id' => $feedItem->id,
        ]);
    });

    it('prevents removing from another users feed', function () {
        $userA = User::factory()->create();
        $tokenA = $userA->createToken('test')->plainTextToken;
        $userB = User::factory()->create();
        $feedB = Feed::factory()->create(['user_id' => $userB->id]);
        $itemB = LibraryItem::factory()->create(['user_id' => $userB->id]);
        $feedItem = FeedItem::factory()->create([
            'feed_id' => $feedB->id,
            'library_item_id' => $itemB->id,
            'sequence' => 0,
        ]);

        $response = $this->withHeader('Authorization', 'Bearer '.$tokenA)
            ->deleteJson('/api/v1/feeds/'.$feedB->id.'/items/'.$feedItem->id);

        $response->assertNotFound();
        $this->assertDatabaseHas('feed_items', [
            'id' => $feedItem->id,
        ]);
    });

    it('does not delete the library item when removing from feed', function () {
        $user = User::factory()->create();
        $token = $user->createToken('test')->plainTextToken;
        $feed = Feed::factory()->create(['user_id' => $user->id]);
        $item = LibraryItem::factory()->create(['user_id' => $user->id]);
        $feedItem = FeedItem::factory()->create([
            'feed_id' => $feed->id,
            'library_item_id' => $item->id,
            'sequence' => 0,
        ]);

        $response = $this->withHeader('Authorization', 'Bearer '.$token)
            ->deleteJson('/api/v1/feeds/'.$feed->id.'/items/'.$feedItem->id);

        $response->assertNoContent();
        $this->assertDatabaseHas('library_items', [
            'id' => $item->id,
        ]);
    });
});
