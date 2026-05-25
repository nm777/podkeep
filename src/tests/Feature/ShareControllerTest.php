<?php

use App\Enums\ProcessingStatusType;
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
});

it('shows share page for public feed', function () {
    $feed = Feed::factory()->create([
        'user_id' => $this->user->id,
        'is_public' => true,
    ]);

    $mediaFile = MediaFile::factory()->create([
        'user_id' => $this->user->id,
        'mime_type' => 'audio/mpeg',
        'duration' => 3600,
    ]);

    $libraryItem = LibraryItem::factory()->create([
        'user_id' => $this->user->id,
        'media_file_id' => $mediaFile->id,
        'processing_status' => ProcessingStatusType::COMPLETED,
    ]);

    FeedItem::factory()->create([
        'feed_id' => $feed->id,
        'library_item_id' => $libraryItem->id,
        'sequence' => 1,
    ]);

    $response = $this->get("/share/{$feed->user_guid}/{$feed->slug}");

    $response->assertSuccessful();
    $response->assertInertia(fn ($page) => $page
        ->where('feed.title', $feed->title)
        ->where('isPublic', true)
        ->has('episodes', 1)
        ->where('episodes.0.title', $libraryItem->title)
    );
});

it('returns 404 for non-existent feed', function () {
    $response = $this->get('/share/00000000-0000-0000-0000-000000000000/nonexistent');

    $response->assertNotFound();
});

it('rejects private feed without token', function () {
    $feed = Feed::factory()->create([
        'user_id' => $this->user->id,
        'is_public' => false,
        'token' => 'secret-token',
    ]);

    $response = $this->get("/share/{$feed->user_guid}/{$feed->slug}");

    $response->assertNotFound();
});

it('grants access to private feed with valid token', function () {
    $feed = Feed::factory()->create([
        'user_id' => $this->user->id,
        'is_public' => false,
        'token' => 'secret-token',
    ]);

    $mediaFile = MediaFile::factory()->create([
        'user_id' => $this->user->id,
        'mime_type' => 'audio/mpeg',
    ]);

    $libraryItem = LibraryItem::factory()->create([
        'user_id' => $this->user->id,
        'media_file_id' => $mediaFile->id,
        'processing_status' => ProcessingStatusType::COMPLETED,
    ]);

    FeedItem::factory()->create([
        'feed_id' => $feed->id,
        'library_item_id' => $libraryItem->id,
        'sequence' => 1,
    ]);

    $response = $this->get("/share/{$feed->user_guid}/{$feed->slug}?token=secret-token");

    $response->assertSuccessful();
    $response->assertInertia(fn ($page) => $page
        ->where('isPublic', false)
        ->has('episodes', 1)
    );
});

it('rejects private feed with wrong token', function () {
    $feed = Feed::factory()->create([
        'user_id' => $this->user->id,
        'is_public' => false,
        'token' => 'secret-token',
    ]);

    $response = $this->get("/share/{$feed->user_guid}/{$feed->slug}?token=wrong-token");

    $response->assertNotFound();
});

it('only shows completed items with media files', function () {
    $feed = Feed::factory()->create([
        'user_id' => $this->user->id,
        'is_public' => true,
    ]);

    $mediaFile = MediaFile::factory()->create([
        'user_id' => $this->user->id,
    ]);

    $completedItem = LibraryItem::factory()->create([
        'user_id' => $this->user->id,
        'media_file_id' => $mediaFile->id,
        'processing_status' => ProcessingStatusType::COMPLETED,
    ]);

    $pendingItem = LibraryItem::factory()->create([
        'user_id' => $this->user->id,
        'media_file_id' => MediaFile::factory()->create(['user_id' => $this->user->id]),
        'processing_status' => ProcessingStatusType::PENDING,
    ]);

    $itemWithoutMedia = LibraryItem::factory()->create([
        'user_id' => $this->user->id,
        'media_file_id' => null,
        'processing_status' => ProcessingStatusType::COMPLETED,
    ]);

    FeedItem::factory()->create(['feed_id' => $feed->id, 'library_item_id' => $completedItem->id, 'sequence' => 1]);
    FeedItem::factory()->create(['feed_id' => $feed->id, 'library_item_id' => $pendingItem->id, 'sequence' => 2]);
    FeedItem::factory()->create(['feed_id' => $feed->id, 'library_item_id' => $itemWithoutMedia->id, 'sequence' => 3]);

    $response = $this->get("/share/{$feed->user_guid}/{$feed->slug}");

    $response->assertSuccessful();
    $response->assertInertia(fn ($page) => $page
        ->has('episodes', 1)
        ->where('episodes.0.title', $completedItem->title)
    );
});

it('orders episodes by sequence ascending', function () {
    $feed = Feed::factory()->create([
        'user_id' => $this->user->id,
        'is_public' => true,
    ]);

    $mediaFile1 = MediaFile::factory()->create(['user_id' => $this->user->id]);
    $mediaFile2 = MediaFile::factory()->create(['user_id' => $this->user->id]);

    $item1 = LibraryItem::factory()->create([
        'user_id' => $this->user->id,
        'media_file_id' => $mediaFile1->id,
        'title' => 'Second Episode',
        'processing_status' => ProcessingStatusType::COMPLETED,
    ]);

    $item2 = LibraryItem::factory()->create([
        'user_id' => $this->user->id,
        'media_file_id' => $mediaFile2->id,
        'title' => 'First Episode',
        'processing_status' => ProcessingStatusType::COMPLETED,
    ]);

    FeedItem::factory()->create(['feed_id' => $feed->id, 'library_item_id' => $item1->id, 'sequence' => 2]);
    FeedItem::factory()->create(['feed_id' => $feed->id, 'library_item_id' => $item2->id, 'sequence' => 1]);

    $response = $this->get("/share/{$feed->user_guid}/{$feed->slug}");

    $response->assertSuccessful();
    $response->assertInertia(fn ($page) => $page
        ->has('episodes', 2)
        ->where('episodes.0.title', 'First Episode')
        ->where('episodes.1.title', 'Second Episode')
    );
});

it('includes feed_token in media URL for private feeds', function () {
    $feed = Feed::factory()->create([
        'user_id' => $this->user->id,
        'is_public' => false,
        'token' => 'secret-token',
    ]);

    $mediaFile = MediaFile::factory()->create([
        'user_id' => $this->user->id,
        'file_path' => 'media/test-audio.mp3',
    ]);

    $libraryItem = LibraryItem::factory()->create([
        'user_id' => $this->user->id,
        'media_file_id' => $mediaFile->id,
        'processing_status' => ProcessingStatusType::COMPLETED,
    ]);

    FeedItem::factory()->create([
        'feed_id' => $feed->id,
        'library_item_id' => $libraryItem->id,
        'sequence' => 1,
    ]);

    $response = $this->get("/share/{$feed->user_guid}/{$feed->slug}?token=secret-token");

    $response->assertSuccessful();
    $response->assertInertia(fn ($page) => $page
        ->where('episodes.0.media_url', '/files/media/test-audio.mp3?feed_token=secret-token')
        ->where('rssUrl', url("/rss/{$feed->user_guid}/{$feed->slug}").'?token=secret-token')
    );
});

it('does not include feed_token in media URL for public feeds', function () {
    $feed = Feed::factory()->create([
        'user_id' => $this->user->id,
        'is_public' => true,
    ]);

    $mediaFile = MediaFile::factory()->create([
        'user_id' => $this->user->id,
        'file_path' => 'media/public-audio.mp3',
    ]);

    $libraryItem = LibraryItem::factory()->create([
        'user_id' => $this->user->id,
        'media_file_id' => $mediaFile->id,
        'processing_status' => ProcessingStatusType::COMPLETED,
    ]);

    FeedItem::factory()->create([
        'feed_id' => $feed->id,
        'library_item_id' => $libraryItem->id,
        'sequence' => 1,
    ]);

    $response = $this->get("/share/{$feed->user_guid}/{$feed->slug}");

    $response->assertSuccessful();
    $response->assertInertia(fn ($page) => $page
        ->where('episodes.0.media_url', '/files/media/public-audio.mp3')
        ->where('rssUrl', url("/rss/{$feed->user_guid}/{$feed->slug}"))
    );
});
