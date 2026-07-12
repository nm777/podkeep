<?php

use App\Enums\ProcessingStatusType;
use App\Jobs\AddLibraryItemToFeedsJob;
use App\Models\Feed;
use App\Models\FeedItem;
use App\Models\LibraryItem;
use App\Models\MediaFile;
use App\Models\User;
use Illuminate\Support\Facades\Storage;

if (! function_exists('attachOrderedFeedItems')) {
    /**
     * Attach three completed library items to a feed with sequences 0, 1, 2.
     *
     * @return array<int, string> Titles indexed by their sequence number.
     */
    function attachOrderedFeedItems(User $user, Feed $feed): array
    {
        $titles = ['Episode Sequence Zero', 'Episode Sequence One', 'Episode Sequence Two'];

        foreach ([0, 1, 2] as $sequence) {
            $mediaFile = MediaFile::factory()->create([
                'user_id' => $user->id,
                'mime_type' => 'audio/mpeg',
                'filesize' => 1000000 + $sequence,
            ]);

            $libraryItem = LibraryItem::factory()->create([
                'user_id' => $user->id,
                'media_file_id' => $mediaFile->id,
                'title' => $titles[$sequence],
                'processing_status' => ProcessingStatusType::COMPLETED,
            ]);

            FeedItem::factory()->create([
                'feed_id' => $feed->id,
                'library_item_id' => $libraryItem->id,
                'sequence' => $sequence,
            ]);
        }

        return $titles;
    }
}

describe('episode_order field storage', function () {
    it('defaults to newest_first when creating a feed', function () {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post('/feeds', [
            'title' => 'My Default Feed',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('feeds', [
            'user_id' => $user->id,
            'title' => 'My Default Feed',
            'episode_order' => 'newest_first',
        ]);
    });

    it('stores chronological episode_order', function () {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post('/feeds', [
            'title' => 'Chronological Feed',
            'episode_order' => 'chronological',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('feeds', [
            'user_id' => $user->id,
            'title' => 'Chronological Feed',
            'episode_order' => 'chronological',
        ]);
    });

    it('updates episode_order on existing feed', function () {
        $user = User::factory()->create();
        $feed = Feed::factory()->create(['user_id' => $user->id]);

        $response = $this->actingAs($user)->put("/feeds/{$feed->id}", [
            'title' => $feed->title,
            'episode_order' => 'chronological',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('feeds', [
            'id' => $feed->id,
            'episode_order' => 'chronological',
        ]);
    });

    it('rejects invalid episode_order value', function () {
        $user = User::factory()->create();

        $response = $this->actingAs($user)
            ->withHeader('Accept', 'application/json')
            ->post('/feeds', [
                'title' => 'Invalid Order Feed',
                'episode_order' => 'invalid',
            ]);

        $response->assertJsonValidationErrors(['episode_order']);
    });
});

describe('RSS feed ordering', function () {
    it('outputs items by sequence ascending for chronological feeds', function () {
        Storage::fake('public');

        $user = User::factory()->create();
        $feed = Feed::factory()->create([
            'user_id' => $user->id,
            'is_public' => true,
            'episode_order' => 'chronological',
        ]);
        $titles = attachOrderedFeedItems($user, $feed);

        $response = $this->get("/rss/{$feed->user_guid}/{$feed->slug}");

        $response->assertStatus(200);
        $content = $response->getContent();

        $posFirst = strpos($content, '<title>'.$titles[0].'</title>');
        $posLast = strpos($content, '<title>'.$titles[2].'</title>');

        expect($posFirst)->not->toBeFalse();
        expect($posLast)->not->toBeFalse();
        expect($posFirst)->toBeLessThan($posLast);
    });

    it('outputs items by sequence descending for newest_first feeds', function () {
        Storage::fake('public');

        $user = User::factory()->create();
        $feed = Feed::factory()->create([
            'user_id' => $user->id,
            'is_public' => true,
            'episode_order' => 'newest_first',
        ]);
        $titles = attachOrderedFeedItems($user, $feed);

        $response = $this->get("/rss/{$feed->user_guid}/{$feed->slug}");

        $response->assertStatus(200);
        $content = $response->getContent();

        $posFirst = strpos($content, '<title>'.$titles[0].'</title>');
        $posLast = strpos($content, '<title>'.$titles[2].'</title>');

        expect($posFirst)->not->toBeFalse();
        expect($posLast)->not->toBeFalse();
        expect($posLast)->toBeLessThan($posFirst);
    });
});

describe('share player ordering', function () {
    it('returns episodes oldest-first for chronological feed', function () {
        $user = User::factory()->create();
        $feed = Feed::factory()->create([
            'user_id' => $user->id,
            'is_public' => true,
            'episode_order' => 'chronological',
        ]);
        $titles = attachOrderedFeedItems($user, $feed);

        $response = $this->get("/share/{$feed->user_guid}/{$feed->slug}");

        $response->assertSuccessful();
        $response->assertInertia(fn ($page) => $page
            ->has('episodes', 3)
            ->where('episodes.0.title', $titles[0])
            ->where('episodes.2.title', $titles[2])
        );
    });

    it('returns episodes newest-first for newest_first feed', function () {
        $user = User::factory()->create();
        $feed = Feed::factory()->create([
            'user_id' => $user->id,
            'is_public' => true,
            'episode_order' => 'newest_first',
        ]);
        $titles = attachOrderedFeedItems($user, $feed);

        $response = $this->get("/share/{$feed->user_guid}/{$feed->slug}");

        $response->assertSuccessful();
        $response->assertInertia(fn ($page) => $page
            ->has('episodes', 3)
            ->where('episodes.0.title', $titles[2])
            ->where('episodes.2.title', $titles[0])
        );
    });
});

describe('feed edit page loading order', function () {
    it('loads items in episode order direction on edit page (newest_first = desc)', function () {
        $user = User::factory()->create();
        $feed = Feed::factory()->create(['user_id' => $user->id]);

        foreach ([2, 0, 1] as $sequence) {
            $libraryItem = LibraryItem::factory()->create(['user_id' => $user->id]);

            FeedItem::factory()->create([
                'feed_id' => $feed->id,
                'library_item_id' => $libraryItem->id,
                'sequence' => $sequence,
            ]);
        }

        $response = $this->actingAs($user)->get("/feeds/{$feed->id}/edit");

        $response->assertOk()
            ->assertInertia(fn ($page) => $page
                ->has('feed.items', 3)
                ->where('feed.items.0.sequence', 2)
                ->where('feed.items.1.sequence', 1)
                ->where('feed.items.2.sequence', 0)
            );
    });

    it('loads items ascending when episode order is chronological', function () {
        $user = User::factory()->create();
        $feed = Feed::factory()->create([
            'user_id' => $user->id,
            'episode_order' => 'chronological',
        ]);

        foreach ([2, 0, 1] as $sequence) {
            $libraryItem = LibraryItem::factory()->create(['user_id' => $user->id]);

            FeedItem::factory()->create([
                'feed_id' => $feed->id,
                'library_item_id' => $libraryItem->id,
                'sequence' => $sequence,
            ]);
        }

        $response = $this->actingAs($user)->get("/feeds/{$feed->id}/edit");

        $response->assertOk()
            ->assertInertia(fn ($page) => $page
                ->has('feed.items', 3)
                ->where('feed.items.0.sequence', 0)
                ->where('feed.items.1.sequence', 1)
                ->where('feed.items.2.sequence', 2)
            );
    });
});

describe('API episode_order exposure', function () {
    it('includes episode_order in API feed response', function () {
        $user = User::factory()->create();
        $token = $user->createToken('test')->plainTextToken;
        $feed = Feed::factory()->create(['user_id' => $user->id]);

        $response = $this->withHeader('Authorization', 'Bearer '.$token)
            ->getJson('/api/v1/feeds/'.$feed->id);

        $response->assertOk();
        $response->assertJsonStructure(['data' => ['episode_order']]);
        $response->assertJsonPath('data.episode_order', 'newest_first');
    });

    it('accepts episode_order on API create', function () {
        $user = User::factory()->create();
        $token = $user->createToken('test')->plainTextToken;

        $response = $this->withHeader('Authorization', 'Bearer '.$token)
            ->postJson('/api/v1/feeds', [
                'title' => 'API Chronological Feed',
                'episode_order' => 'chronological',
            ]);

        $response->assertCreated();
        $response->assertJsonPath('data.episode_order', 'chronological');
        $this->assertDatabaseHas('feeds', [
            'user_id' => $user->id,
            'title' => 'API Chronological Feed',
            'episode_order' => 'chronological',
        ]);
    });
});

describe('auto-append sequence behavior', function () {
    it('appends new items to the end for chronological feeds', function () {
        // Create a chronological feed with 2 items (sequences 0, 1)
        $user = User::factory()->create();
        $feed = Feed::factory()->create(['user_id' => $user->id, 'episode_order' => 'chronological']);
        $item1 = LibraryItem::factory()->create(['user_id' => $user->id]);
        $item2 = LibraryItem::factory()->create(['user_id' => $user->id]);
        FeedItem::factory()->create(['feed_id' => $feed->id, 'library_item_id' => $item1->id, 'sequence' => 0]);
        FeedItem::factory()->create(['feed_id' => $feed->id, 'library_item_id' => $item2->id, 'sequence' => 1]);

        // Add a new item via the job (simulates upload + attach to feed)
        $newItem = LibraryItem::factory()->create(['user_id' => $user->id]);
        dispatch(new AddLibraryItemToFeedsJob($newItem, [$feed->id]));

        // The new item should have sequence 2 (max + 1)
        $newFeedItem = FeedItem::where('feed_id', $feed->id)
            ->where('library_item_id', $newItem->id)
            ->first();
        expect($newFeedItem->sequence)->toBe(2);

        // In chronological (ASC) order, the new item should appear LAST
        $feedItems = $feed->fresh()->items()->orderBy('sequence', 'asc')->get();
        expect($feedItems->last()->library_item_id)->toBe($newItem->id);
    });

    it('places new items at the top for newest_first feeds', function () {
        // Create a newest_first feed with 2 items (sequences 0, 1)
        $user = User::factory()->create();
        $feed = Feed::factory()->create(['user_id' => $user->id, 'episode_order' => 'newest_first']);
        $item1 = LibraryItem::factory()->create(['user_id' => $user->id]);
        $item2 = LibraryItem::factory()->create(['user_id' => $user->id]);
        FeedItem::factory()->create(['feed_id' => $feed->id, 'library_item_id' => $item1->id, 'sequence' => 0]);
        FeedItem::factory()->create(['feed_id' => $feed->id, 'library_item_id' => $item2->id, 'sequence' => 1]);

        // Add a new item via the job
        $newItem = LibraryItem::factory()->create(['user_id' => $user->id]);
        dispatch(new AddLibraryItemToFeedsJob($newItem, [$feed->id]));

        // The new item should have sequence 2 (max + 1 = highest sequence)
        $newFeedItem = FeedItem::where('feed_id', $feed->id)
            ->where('library_item_id', $newItem->id)
            ->first();
        expect($newFeedItem->sequence)->toBe(2);

        // In newest_first (DESC) order, the highest sequence appears FIRST
        $feedItems = $feed->fresh()->items()->reorder()->orderBy('sequence', 'desc')->get();
        expect($feedItems->first()->library_item_id)->toBe($newItem->id);
    });
});
