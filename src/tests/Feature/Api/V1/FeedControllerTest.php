<?php

use App\Models\Feed;
use App\Models\FeedItem;
use App\Models\User;
use Illuminate\Support\Facades\Cache;

describe('feed creation', function () {
    it('creates a feed with valid data', function () {
        $user = User::factory()->create();
        $token = $user->createToken('test')->plainTextToken;

        $response = $this->withHeader('Authorization', 'Bearer '.$token)
            ->postJson('/api/v1/feeds', [
                'title' => 'My Podcast Feed',
            ]);

        $response->assertCreated();
        $response->assertJsonStructure([
            'data' => [
                'id',
                'title',
                'slug',
                'user_guid',
                'token',
            ],
        ]);
        $response->assertJsonPath('data.title', 'My Podcast Feed');
        expect($response->json('data.slug'))->not->toBeEmpty();
        expect($response->json('data.user_guid'))->not->toBeEmpty();
        expect($response->json('data.token'))->not->toBeEmpty();

        $this->assertDatabaseHas('feeds', [
            'user_id' => $user->id,
            'title' => 'My Podcast Feed',
        ]);
    });

    it('validates title is required', function () {
        $user = User::factory()->create();
        $token = $user->createToken('test')->plainTextToken;

        $response = $this->withHeader('Authorization', 'Bearer '.$token)
            ->postJson('/api/v1/feeds', []);

        $response->assertUnprocessable();
        $response->assertJsonValidationErrors('title');
    });

    it('rejects unauthenticated requests', function () {
        $response = $this->postJson('/api/v1/feeds', [
            'title' => 'Should Not Be Created',
        ]);

        $response->assertUnauthorized();
    });

    it('rejects invalid token', function () {
        $response = $this->withHeader('Authorization', 'Bearer invalid')
            ->postJson('/api/v1/feeds', [
                'title' => 'Should Not Be Created',
            ]);

        $response->assertUnauthorized();
    });
});

describe('feed listing', function () {
    it('lists only the authenticated users feeds', function () {
        $userA = User::factory()->create();
        $tokenA = $userA->createToken('test')->plainTextToken;

        $userB = User::factory()->create();

        $feedA1 = Feed::factory()->create(['user_id' => $userA->id, 'title' => 'Feed A1']);
        $feedA2 = Feed::factory()->create(['user_id' => $userA->id, 'title' => 'Feed A2']);
        Feed::factory()->create(['user_id' => $userB->id, 'title' => 'Feed B1']);

        $response = $this->withHeader('Authorization', 'Bearer '.$tokenA)
            ->getJson('/api/v1/feeds');

        $response->assertOk();
        $response->assertJsonStructure(['data' => [['id', 'title']]]);
        $response->assertJsonCount(2, 'data');
        $titles = collect($response->json('data'))->pluck('title');
        expect($titles)->toContain('Feed A1')->toContain('Feed A2');
        expect($titles)->not->toContain('Feed B1');
    });

    it('includes items_count when available', function () {
        $user = User::factory()->create();
        $token = $user->createToken('test')->plainTextToken;

        $feed = Feed::factory()->create(['user_id' => $user->id]);
        FeedItem::factory()->count(3)->create(['feed_id' => $feed->id]);

        $response = $this->withHeader('Authorization', 'Bearer '.$token)
            ->getJson('/api/v1/feeds');

        $response->assertOk();
        $response->assertJsonStructure(['data' => [['items_count']]]);
    });
});

describe('feed show', function () {
    it('shows a feed owned by the user', function () {
        $user = User::factory()->create();
        $token = $user->createToken('test')->plainTextToken;
        $feed = Feed::factory()->create(['user_id' => $user->id]);

        $response = $this->withHeader('Authorization', 'Bearer '.$token)
            ->getJson('/api/v1/feeds/'.$feed->id);

        $response->assertOk();
        $response->assertJsonPath('data.id', $feed->id);
    });

    it('returns 404 for another users feed', function () {
        $userA = User::factory()->create();
        $tokenA = $userA->createToken('test')->plainTextToken;
        $userB = User::factory()->create();
        $feedB = Feed::factory()->create(['user_id' => $userB->id]);

        $response = $this->withHeader('Authorization', 'Bearer '.$tokenA)
            ->getJson('/api/v1/feeds/'.$feedB->id);

        $response->assertNotFound();
    });
});

describe('feed update', function () {
    it('updates a feed', function () {
        $user = User::factory()->create();
        $token = $user->createToken('test')->plainTextToken;
        $feed = Feed::factory()->create(['user_id' => $user->id, 'title' => 'Old Title']);

        Cache::put("rss.{$feed->id}", 'stale-xml', now()->addMinutes(15));

        $response = $this->withHeader('Authorization', 'Bearer '.$token)
            ->putJson('/api/v1/feeds/'.$feed->id, [
                'title' => 'New Title',
            ]);

        $response->assertOk();
        $response->assertJsonPath('data.title', 'New Title');
        expect(Cache::has("rss.{$feed->id}"))->toBeFalse();
    });

    it('prevents updating another users feed', function () {
        $userA = User::factory()->create();
        $tokenA = $userA->createToken('test')->plainTextToken;
        $userB = User::factory()->create();
        $feedB = Feed::factory()->create(['user_id' => $userB->id, 'title' => 'Original Title']);

        $response = $this->withHeader('Authorization', 'Bearer '.$tokenA)
            ->putJson('/api/v1/feeds/'.$feedB->id, [
                'title' => 'Hijacked Title',
            ]);

        expect($response->status())->toBeIn([403, 404]);
        $this->assertDatabaseHas('feeds', [
            'id' => $feedB->id,
            'title' => 'Original Title',
        ]);
    });
});

describe('feed delete', function () {
    it('deletes a feed', function () {
        $user = User::factory()->create();
        $token = $user->createToken('test')->plainTextToken;
        $feed = Feed::factory()->create(['user_id' => $user->id]);

        $response = $this->withHeader('Authorization', 'Bearer '.$token)
            ->deleteJson('/api/v1/feeds/'.$feed->id);

        $response->assertNoContent();
        $this->assertDatabaseMissing('feeds', [
            'id' => $feed->id,
        ]);
    });

    it('prevents deleting another users feed', function () {
        $userA = User::factory()->create();
        $tokenA = $userA->createToken('test')->plainTextToken;
        $userB = User::factory()->create();
        $feedB = Feed::factory()->create(['user_id' => $userB->id]);

        $response = $this->withHeader('Authorization', 'Bearer '.$tokenA)
            ->deleteJson('/api/v1/feeds/'.$feedB->id);

        expect($response->status())->toBeIn([403, 404]);
        $this->assertDatabaseHas('feeds', [
            'id' => $feedB->id,
        ]);
    });
});
