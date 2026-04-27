<?php

use App\Jobs\ProcessYouTubeAudio;
use App\Jobs\RedownloadMediaFile;
use App\Models\LibraryItem;
use App\Models\MediaFile;
use App\Models\User;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Storage;

use function Pest\Laravel\actingAs;

beforeEach(function () {
    Storage::fake('public');

    Http::fake([
        'https://example.com/audio.mp3' => Http::response('RIFFfake audio content', 200),
        'https://example.com/new-audio.mp3' => Http::response('RIFFnew audio content', 200),
        'https://example.com/not-found.mp3' => Http::response('Not Found', 404),
    ]);
});

it('dispatches redownload job to queue', function () {
    Queue::fake();

    $user = User::factory()->create();

    $fileContent = 'RIFFfake audio content';
    $fileHash = hash('sha256', $fileContent);

    Storage::disk('public')->put('media/'.$fileHash.'.mp3', $fileContent);

    $mediaFile = MediaFile::factory()->create([
        'user_id' => $user->id,
        'file_path' => 'media/'.$fileHash.'.mp3',
        'file_hash' => $fileHash,
        'source_url' => 'https://example.com/audio.mp3',
    ]);

    $libraryItem = LibraryItem::factory()->create([
        'user_id' => $user->id,
        'media_file_id' => $mediaFile->id,
        'source_type' => 'url',
    ]);

    actingAs($user)
        ->post("/library/{$libraryItem->id}/redownload")
        ->assertRedirect()
        ->assertSessionHas('success');

    Queue::assertPushed(RedownloadMediaFile::class, function ($job) use ($libraryItem) {
        return $job->getLibraryItemId() === $libraryItem->id;
    });

    expect($libraryItem->fresh()->processing_status->value)->toBe('processing');
});

it('allows user to redownload their own media file', function () {
    $user = User::factory()->create();

    $fileContent = 'RIFFfake audio content';
    $fileHash = hash('sha256', $fileContent);

    Storage::disk('public')->put('media/'.$fileHash.'.mp3', $fileContent);

    $mediaFile = MediaFile::factory()->create([
        'user_id' => $user->id,
        'file_path' => 'media/'.$fileHash.'.mp3',
        'file_hash' => $fileHash,
        'source_url' => 'https://example.com/audio.mp3',
    ]);

    $libraryItem = LibraryItem::factory()->create([
        'user_id' => $user->id,
        'media_file_id' => $mediaFile->id,
        'source_type' => 'url',
    ]);

    actingAs($user)
        ->post("/library/{$libraryItem->id}/redownload")
        ->assertRedirect()
        ->assertSessionHas('success');

    $this->artisan('queue:work --once')->assertExitCode(0);

    $mediaFile->refresh();
    expect($mediaFile->file_hash)->toBe($fileHash);
});

it('updates media file when content has changed', function () {
    $user = User::factory()->create();

    $oldContent = 'RIFFfake audio content';
    $oldHash = hash('sha256', $oldContent);

    Storage::disk('public')->put('media/'.$oldHash.'.mp3', $oldContent);

    $mediaFile = MediaFile::factory()->create([
        'user_id' => $user->id,
        'file_path' => 'media/'.$oldHash.'.mp3',
        'file_hash' => $oldHash,
        'source_url' => 'https://example.com/new-audio.mp3',
    ]);

    $libraryItem = LibraryItem::factory()->create([
        'user_id' => $user->id,
        'media_file_id' => $mediaFile->id,
        'source_type' => 'url',
    ]);

    actingAs($user)
        ->post("/library/{$libraryItem->id}/redownload")
        ->assertRedirect();

    $this->artisan('queue:work --once')->assertExitCode(0);

    $mediaFile->refresh();
    $newHash = hash('sha256', 'RIFFnew audio content');

    expect($mediaFile->file_hash)->toBe($newHash);
    expect($mediaFile->file_path)->toBe('media/'.$newHash.'.mp3');

    Storage::disk('public')->assertMissing('media/'.$oldHash.'.mp3');
    Storage::disk('public')->assertExists('media/'.$newHash.'.mp3');
});

it('restores missing media file when redownloading', function () {
    $user = User::factory()->create();

    $fileContent = 'RIFFfake audio content';
    $fileHash = hash('sha256', $fileContent);

    $mediaFile = MediaFile::factory()->create([
        'user_id' => $user->id,
        'file_path' => 'media/'.$fileHash.'.mp3',
        'file_hash' => $fileHash,
        'source_url' => 'https://example.com/audio.mp3',
    ]);

    $libraryItem = LibraryItem::factory()->create([
        'user_id' => $user->id,
        'media_file_id' => $mediaFile->id,
        'source_type' => 'url',
    ]);

    Storage::disk('public')->assertMissing('media/'.$fileHash.'.mp3');

    actingAs($user)
        ->post("/library/{$libraryItem->id}/redownload")
        ->assertRedirect();

    $this->artisan('queue:work --once')->assertExitCode(0);

    Storage::disk('public')->assertExists('media/'.$fileHash.'.mp3');
    $storedContent = Storage::disk('public')->get('media/'.$fileHash.'.mp3');
    expect($storedContent)->toBe($fileContent);
});

it('prevents user from redownloading another users media', function () {
    $user1 = User::factory()->create();
    $user2 = User::factory()->create();

    $mediaFile = MediaFile::factory()->create([
        'user_id' => $user1->id,
        'source_url' => 'https://example.com/audio.mp3',
    ]);

    $libraryItem = LibraryItem::factory()->create([
        'user_id' => $user1->id,
        'media_file_id' => $mediaFile->id,
    ]);

    actingAs($user2)
        ->post("/library/{$libraryItem->id}/redownload")
        ->assertStatus(403);
});

it('returns error when media file has no source url', function () {
    $user = User::factory()->create();

    $fileContent = 'RIFFfake audio content';
    $fileHash = hash('sha256', $fileContent);

    Storage::disk('public')->put('media/'.$fileHash.'.mp3', $fileContent);

    $mediaFile = MediaFile::factory()->create([
        'user_id' => $user->id,
        'file_path' => 'media/'.$fileHash.'.mp3',
        'file_hash' => $fileHash,
        'source_url' => null,
    ]);

    $libraryItem = LibraryItem::factory()->create([
        'user_id' => $user->id,
        'media_file_id' => $mediaFile->id,
        'source_type' => 'url',
    ]);

    actingAs($user)
        ->post("/library/{$libraryItem->id}/redownload")
        ->assertRedirect()
        ->assertSessionHas('error');
});

it('returns error when library item has no media file', function () {
    $user = User::factory()->create();

    $libraryItem = LibraryItem::factory()->create([
        'user_id' => $user->id,
        'media_file_id' => null,
    ]);

    actingAs($user)
        ->post("/library/{$libraryItem->id}/redownload")
        ->assertRedirect()
        ->assertSessionHas('error');
});

it('returns error when source url returns 404', function () {
    $user = User::factory()->create();

    $fileContent = 'RIFFfake audio content';
    $fileHash = hash('sha256', $fileContent);

    Storage::disk('public')->put('media/'.$fileHash.'.mp3', $fileContent);

    $mediaFile = MediaFile::factory()->create([
        'user_id' => $user->id,
        'file_path' => 'media/'.$fileHash.'.mp3',
        'file_hash' => $fileHash,
        'source_url' => 'https://example.com/not-found.mp3',
    ]);

    $libraryItem = LibraryItem::factory()->create([
        'user_id' => $user->id,
        'media_file_id' => $mediaFile->id,
        'source_type' => 'url',
    ]);

    actingAs($user)
        ->post("/library/{$libraryItem->id}/redownload")
        ->assertRedirect()
        ->assertSessionHas('success');

    $this->artisan('queue:work --once')->assertExitCode(0);

    $libraryItem->refresh();
    expect($libraryItem->processing_status->value)->toBe('failed');
    expect($libraryItem->processing_error)->toContain('Failed to download file');
});

it('dispatches process youtube audio job for youtube items', function () {
    Queue::fake();

    $user = User::factory()->create();

    $mediaFile = MediaFile::factory()->create([
        'user_id' => $user->id,
        'source_url' => 'https://youtube.com/watch?v=test123',
    ]);

    $libraryItem = LibraryItem::factory()->create([
        'user_id' => $user->id,
        'media_file_id' => $mediaFile->id,
        'source_type' => 'youtube',
        'source_url' => 'https://youtube.com/watch?v=test123',
    ]);

    actingAs($user)
        ->post("/library/{$libraryItem->id}/redownload")
        ->assertRedirect()
        ->assertSessionHas('success');

    Queue::assertPushed(ProcessYouTubeAudio::class, function ($job) use ($libraryItem) {
        return $job->getLibraryItemId() === $libraryItem->id;
    });

    expect($libraryItem->fresh()->processing_status->value)->toBe('processing');
});

it('handles redownload when content is html redirect page', function () {
    $user = User::factory()->create();

    Http::fake([
        'https://example.com/redirect.mp3' => Http::response(
            '<!DOCTYPE html><script>window.location.replace("https://example.com/audio.mp3")</script>',
            200
        ),
    ]);

    $fileContent = 'RIFFfake audio content';
    $fileHash = hash('sha256', $fileContent);

    $mediaFile = MediaFile::factory()->create([
        'user_id' => $user->id,
        'file_path' => 'media/'.$fileHash.'.mp3',
        'file_hash' => $fileHash,
        'source_url' => 'https://example.com/redirect.mp3',
    ]);

    $libraryItem = LibraryItem::factory()->create([
        'user_id' => $user->id,
        'media_file_id' => $mediaFile->id,
        'source_type' => 'url',
    ]);

    actingAs($user)
        ->post("/library/{$libraryItem->id}/redownload")
        ->assertRedirect();

    $this->artisan('queue:work --once')->assertExitCode(0);

    Storage::disk('public')->assertExists('media/'.$fileHash.'.mp3');
});
