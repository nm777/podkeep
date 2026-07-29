<?php

use App\Models\Chapter;
use App\Models\LibraryItem;
use App\Models\MediaFile;
use App\Models\User;

it('replaces the full chapter set on sync and deletes chapters not in the payload', function () {
    $user = User::factory()->create();
    $mediaFile = MediaFile::factory()->create(['user_id' => $user->id, 'duration' => 600]);
    $libraryItem = LibraryItem::factory()->create(['user_id' => $user->id, 'media_file_id' => $mediaFile->id]);

    Chapter::factory()->create(['media_file_id' => $mediaFile->id, 'start_time' => 0, 'title' => 'Old Chapter']);

    $response = $this->actingAs($user)->put("/library/{$libraryItem->id}/chapters", [
        'chapters' => [
            ['start_time' => 0, 'title' => 'Intro'],
            ['start_time' => 300, 'title' => 'Main Point'],
        ],
    ]);

    $response->assertRedirect();
    expect(Chapter::where('media_file_id', $mediaFile->id)->count())->toBe(2);
    $this->assertDatabaseMissing('chapters', ['media_file_id' => $mediaFile->id, 'title' => 'Old Chapter']);
    $this->assertDatabaseHas('chapters', ['media_file_id' => $mediaFile->id, 'start_time' => 0, 'title' => 'Intro']);
    $this->assertDatabaseHas('chapters', ['media_file_id' => $mediaFile->id, 'start_time' => 300, 'title' => 'Main Point']);
});

it('clears all chapters when an empty array is synced', function () {
    $user = User::factory()->create();
    $mediaFile = MediaFile::factory()->create(['user_id' => $user->id, 'duration' => 600]);
    $libraryItem = LibraryItem::factory()->create(['user_id' => $user->id, 'media_file_id' => $mediaFile->id]);
    Chapter::factory()->create(['media_file_id' => $mediaFile->id, 'start_time' => 0]);

    $response = $this->actingAs($user)->put("/library/{$libraryItem->id}/chapters", [
        'chapters' => [],
    ]);

    $response->assertRedirect();
    expect(Chapter::where('media_file_id', $mediaFile->id)->count())->toBe(0);
});

it('enforces the 20-chapter maximum', function () {
    $user = User::factory()->create();
    $mediaFile = MediaFile::factory()->create(['user_id' => $user->id, 'duration' => 100000]);
    $libraryItem = LibraryItem::factory()->create(['user_id' => $user->id, 'media_file_id' => $mediaFile->id]);

    $response = $this->actingAs($user)->put("/library/{$libraryItem->id}/chapters", [
        'chapters' => collect(range(0, 20))->map(fn ($i) => ['start_time' => $i * 100, 'title' => "Chapter {$i}"])->all(),
    ]);

    $response->assertSessionHasErrors(['chapters']);
});

it('allows a start time of 0', function () {
    $user = User::factory()->create();
    $mediaFile = MediaFile::factory()->create(['user_id' => $user->id, 'duration' => 600]);
    $libraryItem = LibraryItem::factory()->create(['user_id' => $user->id, 'media_file_id' => $mediaFile->id]);

    $response = $this->actingAs($user)->put("/library/{$libraryItem->id}/chapters", [
        'chapters' => [['start_time' => 0, 'title' => 'Intro']],
    ]);

    $response->assertSessionHasNoErrors();
    $this->assertDatabaseHas('chapters', ['media_file_id' => $mediaFile->id, 'start_time' => 0]);
});

it('rejects a start time at or beyond the media duration', function () {
    $user = User::factory()->create();
    $mediaFile = MediaFile::factory()->create(['user_id' => $user->id, 'duration' => 600]);
    $libraryItem = LibraryItem::factory()->create(['user_id' => $user->id, 'media_file_id' => $mediaFile->id]);

    $response = $this->actingAs($user)->put("/library/{$libraryItem->id}/chapters", [
        'chapters' => [
            ['start_time' => 0, 'title' => 'Intro'],
            ['start_time' => 600, 'title' => 'At End'],
            ['start_time' => 601, 'title' => 'Past End'],
        ],
    ]);

    $response->assertSessionHasErrors(['chapters.1.start_time', 'chapters.2.start_time']);
});

it('rejects duplicate start times', function () {
    $user = User::factory()->create();
    $mediaFile = MediaFile::factory()->create(['user_id' => $user->id, 'duration' => 600]);
    $libraryItem = LibraryItem::factory()->create(['user_id' => $user->id, 'media_file_id' => $mediaFile->id]);

    $response = $this->actingAs($user)->put("/library/{$libraryItem->id}/chapters", [
        'chapters' => [
            ['start_time' => 100, 'title' => 'One'],
            ['start_time' => 100, 'title' => 'Two'],
        ],
    ]);

    $response->assertSessionHasErrors(['chapters']);
});

it('rejects blank chapter titles', function () {
    $user = User::factory()->create();
    $mediaFile = MediaFile::factory()->create(['user_id' => $user->id, 'duration' => 600]);
    $libraryItem = LibraryItem::factory()->create(['user_id' => $user->id, 'media_file_id' => $mediaFile->id]);

    $response = $this->actingAs($user)->put("/library/{$libraryItem->id}/chapters", [
        'chapters' => [['start_time' => 0, 'title' => '']],
    ]);

    $response->assertSessionHasErrors(['chapters.0.title']);
});

it('forbids non-owners from syncing chapters', function () {
    $owner = User::factory()->create();
    $other = User::factory()->create();
    $mediaFile = MediaFile::factory()->create(['user_id' => $owner->id, 'duration' => 600]);
    $libraryItem = LibraryItem::factory()->create(['user_id' => $owner->id, 'media_file_id' => $mediaFile->id]);

    $response = $this->actingAs($other)->put("/library/{$libraryItem->id}/chapters", [
        'chapters' => [['start_time' => 0, 'title' => 'Hacked']],
    ]);

    $response->assertForbidden();
    expect(Chapter::where('media_file_id', $mediaFile->id)->count())->toBe(0);
});

it('forbids shared media references from syncing chapters', function () {
    $owner = User::factory()->create();
    $sharedUser = User::factory()->create();
    $mediaFile = MediaFile::factory()->create(['user_id' => $owner->id, 'duration' => 600]);
    $ownerItem = LibraryItem::factory()->create(['user_id' => $owner->id, 'media_file_id' => $mediaFile->id]);
    $sharedItem = LibraryItem::factory()->create(['user_id' => $sharedUser->id, 'media_file_id' => $mediaFile->id]);

    $this->actingAs($owner)->put("/library/{$ownerItem->id}/chapters", [
        'chapters' => [['start_time' => 0, 'title' => 'Owner Chapter']],
    ])->assertRedirect();

    $this->actingAs($sharedUser)->put("/library/{$sharedItem->id}/chapters", [
        'chapters' => [['start_time' => 0, 'title' => 'Shared User Chapter']],
    ])->assertForbidden();

    $this->assertDatabaseHas('chapters', ['media_file_id' => $mediaFile->id, 'title' => 'Owner Chapter']);
    $this->assertDatabaseMissing('chapters', ['media_file_id' => $mediaFile->id, 'title' => 'Shared User Chapter']);
});
