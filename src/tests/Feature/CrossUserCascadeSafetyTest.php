<?php

use App\Jobs\RedownloadMediaFile;
use App\Models\Feed;
use App\Models\FeedItem;
use App\Models\LibraryItem;
use App\Models\MediaFile;
use App\Models\User;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Storage;

describe('cross-user dedup cascade safety', function () {
    it('does not cascade delete other users library items when media file owner deletes their item', function () {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();

        $mediaFile = MediaFile::factory()->create([
            'user_id' => $user1->id,
            'file_hash' => 'shared-hash-abc123',
        ]);

        $item1 = LibraryItem::factory()->create([
            'user_id' => $user1->id,
            'media_file_id' => $mediaFile->id,
        ]);

        $item2 = LibraryItem::factory()->create([
            'user_id' => $user2->id,
            'media_file_id' => $mediaFile->id,
        ]);

        $item1->delete();

        expect(LibraryItem::find($item2->id))->not->toBeNull();
        expect(MediaFile::find($mediaFile->id))->not->toBeNull();
    });

    it('revokes anonymous access when an owner deletes its last public item while shared media remains', function () {
        Storage::fake('media');

        $owner = User::factory()->create();
        $linkedUser = User::factory()->create();
        $mediaFile = MediaFile::factory()->create([
            'user_id' => $owner->id,
            'file_path' => 'media/shared-owner-item.mp3',
        ]);
        Storage::disk('media')->put($mediaFile->file_path, 'audio');

        $ownerItem = LibraryItem::factory()->create([
            'user_id' => $owner->id,
            'media_file_id' => $mediaFile->id,
        ]);
        LibraryItem::factory()->create([
            'user_id' => $linkedUser->id,
            'media_file_id' => $mediaFile->id,
        ]);
        $feed = Feed::factory()->create(['user_id' => $owner->id, 'is_public' => true]);
        FeedItem::factory()->create(['feed_id' => $feed->id, 'library_item_id' => $ownerItem->id]);
        MediaFile::syncPublicStatusForUser($owner->id);

        $this->get("/files/{$mediaFile->file_path}")->assertSuccessful();

        $ownerItem->delete();

        expect(MediaFile::find($mediaFile->id))->not->toBeNull();
        expect($mediaFile->refresh()->is_public)->toBeFalse();
        $this->get("/files/{$mediaFile->file_path}")->assertForbidden();
    });

    it('sets media_file_id to null instead of cascade deleting when media file is deleted', function () {
        $user = User::factory()->create();

        $mediaFile = MediaFile::factory()->create([
            'user_id' => $user->id,
        ]);

        $item = LibraryItem::factory()->create([
            'user_id' => $user->id,
            'media_file_id' => $mediaFile->id,
        ]);

        $mediaFile->delete();

        $item->refresh();
        expect($item->media_file_id)->toBeNull();
        expect(LibraryItem::find($item->id))->not->toBeNull();
    });

    it('preserves shared media when its owner deletes their account', function () {
        Queue::fake();
        Storage::fake('media');

        $owner = User::factory()->create();
        $linkedUser = User::factory()->create();

        $mediaFile = MediaFile::factory()->create([
            'user_id' => $owner->id,
            'file_hash' => 'shared-hash-owner-deletion',
            'source_url' => 'https://example.com/audio.mp3',
            'file_path' => 'media/shared-owner-account.mp3',
        ]);
        Storage::disk('media')->put($mediaFile->file_path, 'audio');

        $ownerItem = LibraryItem::factory()->create([
            'user_id' => $owner->id,
            'media_file_id' => $mediaFile->id,
        ]);

        $linkedItem = LibraryItem::factory()->create([
            'user_id' => $linkedUser->id,
            'media_file_id' => $mediaFile->id,
            'source_type' => 'url',
        ]);
        $feed = Feed::factory()->create(['user_id' => $owner->id, 'is_public' => true]);
        FeedItem::factory()->create(['feed_id' => $feed->id, 'library_item_id' => $ownerItem->id]);
        MediaFile::syncPublicStatusForUser($owner->id);

        $this->get("/files/{$mediaFile->file_path}")->assertSuccessful();

        $this->actingAs($owner)
            ->delete('/settings/profile', ['password' => 'password'])
            ->assertSessionHasNoErrors()
            ->assertRedirect('/');

        expect($linkedItem->refresh()->media_file_id)->toBe($mediaFile->id);
        $preservedMediaFile = MediaFile::find($mediaFile->id);

        expect($preservedMediaFile)->not->toBeNull();
        expect($preservedMediaFile->user_id)->toBe($linkedUser->id);
        expect($preservedMediaFile->is_public)->toBeFalse();
        $this->get("/files/{$mediaFile->file_path}")->assertForbidden();

        $this->actingAs($linkedUser)
            ->post("/library/{$linkedItem->id}/redownload")
            ->assertRedirect()
            ->assertSessionHas('success');

        Queue::assertPushed(RedownloadMediaFile::class, function (RedownloadMediaFile $job) use ($linkedItem): bool {
            return $job->getLibraryItemId() === $linkedItem->id;
        });
    });
});
