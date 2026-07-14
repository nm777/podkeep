<?php

use App\Models\Feed;
use App\Models\FeedItem;
use App\Models\LibraryItem;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Storage;

describe('media upload', function () {
    it('uploads a media file successfully', function () {
        Storage::fake('public');
        Queue::fake();

        $user = User::factory()->create();
        $token = $user->createToken('test')->plainTextToken;
        $file = UploadedFile::fake()->createWithContent('test.mp3', 'fake audio content');

        $response = $this->withHeader('Authorization', 'Bearer '.$token)
            ->post('/api/v1/library', [
                'title' => 'Test Upload',
                'file' => $file,
            ]);

        $response->assertCreated();
        $response->assertJsonStructure([
            'data' => [
                'id',
                'title',
                'processing_status',
            ],
        ]);
        $response->assertJsonPath('data.title', 'Test Upload');
        $response->assertJsonPath('data.processing_status', 'pending');

        $this->assertDatabaseHas('library_items', [
            'user_id' => $user->id,
            'title' => 'Test Upload',
            'source_type' => 'upload',
        ]);
    });

    it('validates title is required', function () {
        Storage::fake('public');
        Queue::fake();

        $user = User::factory()->create();
        $token = $user->createToken('test')->plainTextToken;
        $file = UploadedFile::fake()->createWithContent('test.mp3', 'fake audio content');

        $response = $this->withHeader('Authorization', 'Bearer '.$token)
            ->withHeader('Accept', 'application/json')
            ->post('/api/v1/library', [
                'file' => $file,
            ]);

        $response->assertUnprocessable();
        $response->assertJsonValidationErrors('title');
    });

    it('rejects unsupported file types', function () {
        Storage::fake('public');
        Queue::fake();

        $user = User::factory()->create();
        $token = $user->createToken('test')->plainTextToken;
        $file = UploadedFile::fake()->create('document.txt', 100, 'text/plain');

        $response = $this->withHeader('Authorization', 'Bearer '.$token)
            ->withHeader('Accept', 'application/json')
            ->post('/api/v1/library', [
                'title' => 'Bad File',
                'file' => $file,
            ]);

        $response->assertUnprocessable();
        $response->assertJsonValidationErrors('file');
    });

    it('rejects unauthenticated upload', function () {
        $response = $this->withHeader('Accept', 'application/json')
            ->post('/api/v1/library', [
                'title' => 'Should Not Be Created',
            ]);

        $response->assertUnauthorized();
    });
});

describe('media via URL', function () {
    it('creates a library item from URL', function () {
        Queue::fake();

        $user = User::factory()->create();
        $token = $user->createToken('test')->plainTextToken;

        $response = $this->withHeader('Authorization', 'Bearer '.$token)
            ->postJson('/api/v1/library', [
                'title' => 'URL Audio',
                'url' => 'https://example.com/episode.mp3',
            ]);

        $response->assertCreated();
        $response->assertJsonPath('data.title', 'URL Audio');
        $response->assertJsonPath('data.source_type', 'url');

        $this->assertDatabaseHas('library_items', [
            'user_id' => $user->id,
            'title' => 'URL Audio',
            'source_type' => 'url',
            'source_url' => 'https://example.com/episode.mp3',
        ]);
    });

    it('validates URL format', function () {
        $user = User::factory()->create();
        $token = $user->createToken('test')->plainTextToken;

        $response = $this->withHeader('Authorization', 'Bearer '.$token)
            ->postJson('/api/v1/library', [
                'title' => 'Bad URL Audio',
                'url' => 'https://example.com/not-a-media-page',
            ]);

        $response->assertUnprocessable();
        $response->assertJsonValidationErrors('url');
    });
});

describe('library listing', function () {
    it('lists only the authenticated users items', function () {
        $userA = User::factory()->create();
        $tokenA = $userA->createToken('test')->plainTextToken;

        $userB = User::factory()->create();

        LibraryItem::factory()->create(['user_id' => $userA->id, 'title' => 'Item A1']);
        LibraryItem::factory()->create(['user_id' => $userA->id, 'title' => 'Item A2']);
        LibraryItem::factory()->create(['user_id' => $userB->id, 'title' => 'Item B1']);

        $response = $this->withHeader('Authorization', 'Bearer '.$tokenA)
            ->getJson('/api/v1/library');

        $response->assertOk();
        $response->assertJsonCount(2, 'data');
        $titles = collect($response->json('data'))->pluck('title');
        expect($titles)->toContain('Item A1')->toContain('Item A2');
        expect($titles)->not->toContain('Item B1');
    });

    it('includes media file details', function () {
        $user = User::factory()->create();
        $token = $user->createToken('test')->plainTextToken;

        $item = LibraryItem::factory()->create(['user_id' => $user->id]);

        $response = $this->withHeader('Authorization', 'Bearer '.$token)
            ->getJson('/api/v1/library/'.$item->id);

        $response->assertOk();
        $response->assertJsonPath('data.id', $item->id);
        $response->assertJsonStructure([
            'data' => [
                'media_file' => [
                    'id',
                    'file_hash',
                    'mime_type',
                ],
            ],
        ]);
    });
});

describe('library update', function () {
    it('updates item metadata', function () {
        $user = User::factory()->create();
        $token = $user->createToken('test')->plainTextToken;
        $item = LibraryItem::factory()->create([
            'user_id' => $user->id,
            'title' => 'Old Title',
        ]);

        $response = $this->withHeader('Authorization', 'Bearer '.$token)
            ->putJson('/api/v1/library/'.$item->id, [
                'title' => 'New Title',
            ]);

        $response->assertOk();
        $response->assertJsonPath('data.title', 'New Title');

        $this->assertDatabaseHas('library_items', [
            'id' => $item->id,
            'title' => 'New Title',
        ]);
    });

    it('prevents updating another users item', function () {
        $userA = User::factory()->create();
        $tokenA = $userA->createToken('test')->plainTextToken;

        $userB = User::factory()->create();
        $itemB = LibraryItem::factory()->create([
            'user_id' => $userB->id,
            'title' => 'Original Title',
        ]);

        $response = $this->withHeader('Authorization', 'Bearer '.$tokenA)
            ->putJson('/api/v1/library/'.$itemB->id, [
                'title' => 'Hijacked Title',
            ]);

        expect($response->status())->toBeIn([403, 404]);
        $this->assertDatabaseHas('library_items', [
            'id' => $itemB->id,
            'title' => 'Original Title',
        ]);
    });

    it('invalidates the rss cache for feeds containing the item', function () {
        $user = User::factory()->create();
        $token = $user->createToken('test')->plainTextToken;

        $item = LibraryItem::factory()->create(['user_id' => $user->id]);
        $feed = Feed::factory()->create(['user_id' => $user->id]);
        FeedItem::factory()->create([
            'feed_id' => $feed->id,
            'library_item_id' => $item->id,
        ]);

        Cache::put("rss.{$feed->id}", 'stale-xml', now()->addMinutes(15));

        $this->withHeader('Authorization', 'Bearer '.$token)
            ->putJson('/api/v1/library/'.$item->id, ['title' => 'New Title']);

        expect(Cache::has("rss.{$feed->id}"))->toBeFalse();
    });
});

describe('library delete', function () {
    it('deletes a library item', function () {
        Storage::fake('public');

        $user = User::factory()->create();
        $token = $user->createToken('test')->plainTextToken;
        $item = LibraryItem::factory()->create(['user_id' => $user->id]);

        $response = $this->withHeader('Authorization', 'Bearer '.$token)
            ->deleteJson('/api/v1/library/'.$item->id);

        $response->assertNoContent();
        $this->assertDatabaseMissing('library_items', [
            'id' => $item->id,
        ]);
    });

    it('prevents deleting another users item', function () {
        $userA = User::factory()->create();
        $tokenA = $userA->createToken('test')->plainTextToken;

        $userB = User::factory()->create();
        $itemB = LibraryItem::factory()->create(['user_id' => $userB->id]);

        $response = $this->withHeader('Authorization', 'Bearer '.$tokenA)
            ->deleteJson('/api/v1/library/'.$itemB->id);

        expect($response->status())->toBeIn([403, 404]);
        $this->assertDatabaseHas('library_items', [
            'id' => $itemB->id,
        ]);
    });
});
