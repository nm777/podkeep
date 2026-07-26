<?php

use App\Models\Chapter;
use App\Models\Feed;
use App\Models\FeedItem;
use App\Models\LibraryItem;
use App\Models\MediaFile;
use App\Models\User;
use Illuminate\Support\Facades\Cache;

/**
 * @return array{0: Feed, 1: LibraryItem}
 */
function chapterFeedWithItem(User $user, MediaFile $mediaFile): array
{
    $feed = Feed::factory()->create(['user_id' => $user->id, 'is_public' => true]);
    $libraryItem = LibraryItem::factory()->create(['user_id' => $user->id, 'media_file_id' => $mediaFile->id]);
    FeedItem::factory()->create([
        'feed_id' => $feed->id,
        'library_item_id' => $libraryItem->id,
        'sequence' => 0,
    ]);

    return [$feed, $libraryItem];
}

it('includes psc:chapters in the RSS item when the media file has chapters', function () {
    $user = User::factory()->create();
    $mediaFile = MediaFile::factory()->create(['user_id' => $user->id, 'duration' => 3800]);
    [$feed, $libraryItem] = chapterFeedWithItem($user, $mediaFile);

    Chapter::factory()->create(['media_file_id' => $mediaFile->id, 'start_time' => 0, 'title' => 'Intro']);
    Chapter::factory()->create(['media_file_id' => $mediaFile->id, 'start_time' => 330, 'title' => 'Main Point']);

    $xml = $this->get(route('rss.show', ['user_guid' => $feed->user_guid, 'feed_slug' => $feed->slug]))->content();

    expect($xml)->toContain('xmlns:psc="http://podlove.org/simple-chapters"');
    expect($xml)->toContain('<psc:chapters');
    expect($xml)->toContain('start="0:00:00" title="Intro"');
    expect($xml)->toContain('start="0:05:30" title="Main Point"');
});

it('omits any chapter element when the media file has no chapters', function () {
    $user = User::factory()->create();
    $mediaFile = MediaFile::factory()->create(['user_id' => $user->id, 'duration' => 600]);
    [$feed, $libraryItem] = chapterFeedWithItem($user, $mediaFile);

    $xml = $this->get(route('rss.show', ['user_guid' => $feed->user_guid, 'feed_slug' => $feed->slug]))->content();

    expect($xml)->not->toContain('<psc:chapters');
});

it('produces valid RSS XML for a chaptered feed', function () {
    $user = User::factory()->create();
    $mediaFile = MediaFile::factory()->create(['user_id' => $user->id, 'duration' => 600]);
    [$feed, $libraryItem] = chapterFeedWithItem($user, $mediaFile);
    Chapter::factory()->create(['media_file_id' => $mediaFile->id, 'start_time' => 0, 'title' => 'Intro']);

    // RssController throws (500) on malformed XML; a 200 means valid XML.
    $this->get(route('rss.show', ['user_guid' => $feed->user_guid, 'feed_slug' => $feed->slug]))->assertOk();
});

it('clears the rss feed cache when chapters are synced', function () {
    $user = User::factory()->create();
    $mediaFile = MediaFile::factory()->create(['user_id' => $user->id, 'duration' => 600]);
    [$feed, $libraryItem] = chapterFeedWithItem($user, $mediaFile);

    // Prime the cache.
    $this->get(route('rss.show', ['user_guid' => $feed->user_guid, 'feed_slug' => $feed->slug]))->assertOk();
    expect(Cache::has("rss.{$feed->id}"))->toBeTrue();

    $this->actingAs($user)->put("/library/{$libraryItem->id}/chapters", [
        'chapters' => [['start_time' => 0, 'title' => 'New Chapter']],
    ])->assertRedirect();

    expect(Cache::has("rss.{$feed->id}"))->toBeFalse();
});
