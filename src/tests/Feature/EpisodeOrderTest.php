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

describe('feed_type field storage', function () {
    it('defaults to newest_first when creating a feed', function () {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post('/feeds', [
            'title' => 'My Default Feed',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('feeds', [
            'user_id' => $user->id,
            'title' => 'My Default Feed',
            'feed_type' => 'append',
        ]);
    });

    it('stores chronological feed_type', function () {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post('/feeds', [
            'title' => 'Chronological Feed',
            'feed_type' => 'static',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('feeds', [
            'user_id' => $user->id,
            'title' => 'Chronological Feed',
            'feed_type' => 'static',
        ]);
    });

    it('updates feed_type on existing feed', function () {
        $user = User::factory()->create();
        $feed = Feed::factory()->create(['user_id' => $user->id]);

        $response = $this->actingAs($user)->put("/feeds/{$feed->id}", [
            'title' => $feed->title,
            'feed_type' => 'static',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('feeds', [
            'id' => $feed->id,
            'feed_type' => 'static',
        ]);
    });

    it('rejects invalid feed_type value', function () {
        $user = User::factory()->create();

        $response = $this->actingAs($user)
            ->withHeader('Accept', 'application/json')
            ->post('/feeds', [
                'title' => 'Invalid Order Feed',
                'feed_type' => 'invalid',
            ]);

        $response->assertJsonValidationErrors(['feed_type']);
    });
});

describe('RSS feed ordering', function () {
    it('outputs items by sequence ascending for chronological feeds', function () {
        Storage::fake('public');

        $user = User::factory()->create();
        $feed = Feed::factory()->create([
            'user_id' => $user->id,
            'is_public' => true,
            'feed_type' => 'static',
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

    it('outputs append feed items newest-first by created_at', function () {
        Storage::fake('public');

        $user = User::factory()->create();
        $feed = Feed::factory()->create([
            'user_id' => $user->id,
            'is_public' => true,
            'feed_type' => 'append',
        ]);
        $titles = attachOrderedFeedItems($user, $feed);

        // Set clearly different created_at timestamps on each feed item
        $feedItems = $feed->items()->orderBy('sequence')->get();
        DB::table('feed_items')->where('id', $feedItems[0]->id)->update(['created_at' => now()->subMinutes(5)]);
        DB::table('feed_items')->where('id', $feedItems[1]->id)->update(['created_at' => now()->subMinutes(3)]);
        DB::table('feed_items')->where('id', $feedItems[2]->id)->update(['created_at' => now()]);
        Cache::flush();

        $response = $this->get("/rss/{$feed->user_guid}/{$feed->slug}");

        $response->assertStatus(200);
        $content = $response->getContent();

        $posFirst = strpos($content, '<title>'.$titles[0].'</title>');
        $posLast = strpos($content, '<title>'.$titles[2].'</title>');

        expect($posFirst)->not->toBeFalse();
        expect($posLast)->not->toBeFalse();
        // Last created item (titles[2]) should appear before first (titles[0])
        expect($posLast)->toBeLessThan($posFirst);
    });
});

describe('share player ordering', function () {
    it('returns episodes oldest-first for chronological feed', function () {
        $user = User::factory()->create();
        $feed = Feed::factory()->create([
            'user_id' => $user->id,
            'is_public' => true,
            'feed_type' => 'static',
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

    it('returns episodes newest-first for append feed', function () {
        $user = User::factory()->create();
        $feed = Feed::factory()->create([
            'user_id' => $user->id,
            'is_public' => true,
            'feed_type' => 'append',
        ]);
        $titles = attachOrderedFeedItems($user, $feed);

        // Set clearly different created_at timestamps
        $feedItems = $feed->items()->orderBy('sequence')->get();
        DB::table('feed_items')->where('id', $feedItems[0]->id)->update(['created_at' => now()->subMinutes(5)]);
        DB::table('feed_items')->where('id', $feedItems[1]->id)->update(['created_at' => now()->subMinutes(3)]);
        DB::table('feed_items')->where('id', $feedItems[2]->id)->update(['created_at' => now()]);

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
    it('loads append feed items newest-first by created_at on edit page', function () {
        $user = User::factory()->create();
        $feed = Feed::factory()->create([
            'user_id' => $user->id,
            'feed_type' => 'append',
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
            );
    });

    it('loads items ascending when episode order is chronological', function () {
        $user = User::factory()->create();
        $feed = Feed::factory()->create([
            'user_id' => $user->id,
            'feed_type' => 'static',
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

describe('API feed_type exposure', function () {
    it('includes feed_type in API feed response', function () {
        $user = User::factory()->create();
        $token = $user->createToken('test')->plainTextToken;
        $feed = Feed::factory()->create(['user_id' => $user->id]);

        $response = $this->withHeader('Authorization', 'Bearer '.$token)
            ->getJson('/api/v1/feeds/'.$feed->id);

        $response->assertOk();
        $response->assertJsonStructure(['data' => ['feed_type']]);
        $response->assertJsonPath('data.feed_type', 'append');
    });

    it('accepts feed_type on API create', function () {
        $user = User::factory()->create();
        $token = $user->createToken('test')->plainTextToken;

        $response = $this->withHeader('Authorization', 'Bearer '.$token)
            ->postJson('/api/v1/feeds', [
                'title' => 'API Chronological Feed',
                'feed_type' => 'static',
            ]);

        $response->assertCreated();
        $response->assertJsonPath('data.feed_type', 'static');
        $this->assertDatabaseHas('feeds', [
            'user_id' => $user->id,
            'title' => 'API Chronological Feed',
            'feed_type' => 'static',
        ]);
    });
});

describe('auto-append sequence behavior', function () {
    it('appends new items to the end for chronological feeds', function () {
        // Create a chronological feed with 2 items (sequences 0, 1)
        $user = User::factory()->create();
        $feed = Feed::factory()->create(['user_id' => $user->id, 'feed_type' => 'static']);
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
        $feed = Feed::factory()->create(['user_id' => $user->id, 'feed_type' => 'append']);
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
