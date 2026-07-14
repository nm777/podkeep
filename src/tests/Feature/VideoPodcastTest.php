<?php

use App\Models\Feed;
use App\Models\LibraryItem;
use App\Models\MediaFile;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->user = User::factory()->create([
        'email_verified_at' => now(),
        'approval_status' => 'approved',
    ]);
});

it('uses video MIME type in RSS enclosure for video items', function () {
    $feed = Feed::factory()->create([
        'user_id' => $this->user->id,
        'feed_type' => 'static',
        'is_public' => true,
    ]);

    $mediaFile = MediaFile::factory()->create([
        'user_id' => $this->user->id,
        'mime_type' => 'video/mp4',
    ]);

    $libraryItem = LibraryItem::factory()->create([
        'user_id' => $this->user->id,
        'media_file_id' => $mediaFile->id,
        'media_type' => 'video',
        'processing_status' => 'completed',
    ]);

    $feed->items()->create([
        'library_item_id' => $libraryItem->id,
        'sequence' => 0,
    ]);

    $response = $this->get("/rss/{$feed->user_guid}/{$feed->slug}");

    $response->assertOk();
    $content = $response->content();
    expect($content)->toContain('type="video/mp4"');
});

it('uses audio MIME type for audio items in same feed', function () {
    $feed = Feed::factory()->create([
        'user_id' => $this->user->id,
        'feed_type' => 'static',
        'is_public' => true,
    ]);

    $videoMedia = MediaFile::factory()->create([
        'user_id' => $this->user->id,
        'mime_type' => 'video/mp4',
    ]);
    $audioMedia = MediaFile::factory()->create([
        'user_id' => $this->user->id,
        'mime_type' => 'audio/mpeg',
    ]);

    $videoItem = LibraryItem::factory()->create([
        'user_id' => $this->user->id,
        'media_file_id' => $videoMedia->id,
        'media_type' => 'video',
        'processing_status' => 'completed',
    ]);
    $audioItem = LibraryItem::factory()->create([
        'user_id' => $this->user->id,
        'media_file_id' => $audioMedia->id,
        'media_type' => 'audio',
        'processing_status' => 'completed',
    ]);

    $feed->items()->createMany([
        ['library_item_id' => $videoItem->id, 'sequence' => 0],
        ['library_item_id' => $audioItem->id, 'sequence' => 1],
    ]);

    $response = $this->get("/rss/{$feed->user_guid}/{$feed->slug}");

    $response->assertOk();
    $content = $response->content();
    expect($content)->toContain('type="video/mp4"');
    expect($content)->toContain('type="audio/mpeg"');
});
