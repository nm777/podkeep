<?php

use App\Models\Feed;
use App\Models\FeedItem;
use App\Models\LibraryItem;
use App\Models\MediaFile;
use App\Models\User;

it('attaches a library item to a feed', function () {
    $user = User::factory()->create();
    $feed = Feed::factory()->create(['user_id' => $user->id]);
    $mediaFile = MediaFile::factory()->create(['user_id' => $user->id]);
    $libraryItem = LibraryItem::factory()->create(['user_id' => $user->id, 'media_file_id' => $mediaFile->id]);

    $response = $this->actingAs($user)->post("/library/{$libraryItem->id}/feeds", ['feed_id' => $feed->id]);

    $response->assertRedirect();
    $this->assertDatabaseHas('feed_items', ['feed_id' => $feed->id, 'library_item_id' => $libraryItem->id]);
});

it('prevents adding another users feed', function () {
    $owner = User::factory()->create();
    $other = User::factory()->create();
    $feed = Feed::factory()->create(['user_id' => $other->id]);
    $mediaFile = MediaFile::factory()->create(['user_id' => $owner->id]);
    $libraryItem = LibraryItem::factory()->create(['user_id' => $owner->id, 'media_file_id' => $mediaFile->id]);

    $response = $this->actingAs($owner)->post("/library/{$libraryItem->id}/feeds", ['feed_id' => $feed->id]);

    $response->assertSessionHasErrors(['feed_id']);
});

it('forbids non-owners from attaching', function () {
    $owner = User::factory()->create();
    $other = User::factory()->create();
    $feed = Feed::factory()->create(['user_id' => $owner->id]);
    $mediaFile = MediaFile::factory()->create(['user_id' => $owner->id]);
    $libraryItem = LibraryItem::factory()->create(['user_id' => $owner->id, 'media_file_id' => $mediaFile->id]);

    $response = $this->actingAs($other)->post("/library/{$libraryItem->id}/feeds", ['feed_id' => $feed->id]);

    $response->assertForbidden();
});

it('ignores duplicate attachment', function () {
    $user = User::factory()->create();
    $feed = Feed::factory()->create(['user_id' => $user->id]);
    $mediaFile = MediaFile::factory()->create(['user_id' => $user->id]);
    $libraryItem = LibraryItem::factory()->create(['user_id' => $user->id, 'media_file_id' => $mediaFile->id]);
    FeedItem::factory()->create(['feed_id' => $feed->id, 'library_item_id' => $libraryItem->id, 'sequence' => 0]);

    $response = $this->actingAs($user)->post("/library/{$libraryItem->id}/feeds", ['feed_id' => $feed->id]);

    $response->assertRedirect();
    expect(FeedItem::where('feed_id', $feed->id)->where('library_item_id', $libraryItem->id)->count())->toBe(1);
});
