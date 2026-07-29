<?php

use App\Enums\ProcessingStatusType;
use App\Jobs\ProcessYouTubeAudio;
use App\Jobs\RedownloadMediaFile;
use App\Models\Chapter;
use App\Models\LibraryItem;
use App\Models\MediaFile;
use App\Models\User;
use App\Services\MediaProcessing\MediaDownloader;
use App\Services\MediaProcessing\MediaRedownloader;
use App\Services\MediaProcessing\MediaStorageManager;
use App\Services\MediaProcessing\MediaValidator;
use Illuminate\Support\Facades\DB;
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

it('resolves the redownloader when dry-running the media redownload command', function () {
    $user = User::factory()->create();
    $mediaFile = MediaFile::factory()->create([
        'user_id' => $user->id,
        'source_url' => 'https://example.com/audio.mp3',
    ]);

    LibraryItem::factory()->create([
        'user_id' => $user->id,
        'media_file_id' => $mediaFile->id,
    ]);

    $this->artisan('media:redownload', ['--dry-run' => true])
        ->expectsOutput('  Would redownload from: https://example.com/audio.mp3')
        ->assertExitCode(0);
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
        'transcript' => [['start' => 0, 'end' => 5, 'text' => 'cached']],
        'chapter_generation_status' => 'failed',
        'chapter_proposal' => [['start_time' => 0, 'title' => 'Intro']],
        'chapter_proposal_for_hash' => $oldHash,
        'chapter_generation_error' => 'Generation failed',
    ]);
    Chapter::factory()->create(['media_file_id' => $mediaFile->id]);

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
    expect($mediaFile->transcript)->toBeNull();
    expect($mediaFile->chapter_generation_status)->toBeNull();
    expect($mediaFile->chapter_proposal)->toBeNull();
    expect($mediaFile->chapter_proposal_for_hash)->toBeNull();
    expect($mediaFile->chapter_generation_error)->toBeNull();
    expect($mediaFile->chapters)->toHaveCount(0);

    Storage::disk('public')->assertMissing('media/'.$oldHash.'.mp3');
    Storage::disk('public')->assertExists('media/'.$newHash.'.mp3');
});

it('relinks only the requested item when redownloading shared media with changed content', function () {
    $owner = User::factory()->create();
    $otherUser = User::factory()->create();
    $oldContent = 'RIFFfake audio content';
    $oldHash = hash('sha256', $oldContent);
    $newHash = hash('sha256', 'RIFFnew audio content');

    Storage::disk('public')->put('media/'.$oldHash.'.mp3', $oldContent);

    $mediaFile = MediaFile::factory()->create([
        'user_id' => $owner->id,
        'file_path' => 'media/'.$oldHash.'.mp3',
        'file_hash' => $oldHash,
        'source_url' => 'https://example.com/new-audio.mp3',
        'transcript' => [['start' => 0, 'end' => 5, 'text' => 'cached']],
    ]);
    $requestedItem = LibraryItem::factory()->create([
        'user_id' => $owner->id,
        'media_file_id' => $mediaFile->id,
        'source_type' => 'url',
    ]);
    $otherItem = LibraryItem::factory()->create([
        'user_id' => $otherUser->id,
        'media_file_id' => $mediaFile->id,
        'source_type' => 'url',
    ]);

    actingAs($owner)
        ->post("/library/{$requestedItem->id}/redownload")
        ->assertRedirect();

    $this->artisan('queue:work --once')->assertExitCode(0);

    expect($requestedItem->fresh()->media_file_id)->not->toBe($mediaFile->id);
    expect($otherItem->fresh()->media_file_id)->toBe($mediaFile->id);
    expect($mediaFile->fresh()->only(['file_hash', 'file_path', 'transcript']))->toBe([
        'file_hash' => $oldHash,
        'file_path' => 'media/'.$oldHash.'.mp3',
        'transcript' => [['start' => 0, 'end' => 5, 'text' => 'cached']],
    ]);
    expect($requestedItem->fresh()->mediaFile->file_hash)->toBe($newHash);

    Storage::disk('public')->assertExists('media/'.$oldHash.'.mp3');
    Storage::disk('public')->assertExists('media/'.$newHash.'.mp3');
});

it('removes a newly moved file when redownload persistence fails', function () {
    $user = User::factory()->create();
    $oldContent = 'RIFFfake audio content';
    $oldHash = hash('sha256', $oldContent);
    $newContent = 'RIFFnew audio content';
    $newHash = hash('sha256', $newContent);

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
    ]);

    DB::shouldReceive('transaction')->once()->andThrow(new RuntimeException('Database unavailable'));

    expect(fn () => app(MediaRedownloader::class)->redownload($libraryItem))
        ->toThrow(RuntimeException::class, 'Database unavailable');

    Storage::disk('public')->assertExists('media/'.$oldHash.'.mp3');
    Storage::disk('public')->assertMissing('media/'.$newHash.'.mp3');
    expect($mediaFile->fresh()->only(['file_path', 'file_hash']))->toBe([
        'file_path' => 'media/'.$oldHash.'.mp3',
        'file_hash' => $oldHash,
    ]);
});

it('removes the downloaded temp file when redownload validation fails', function () {
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
    ]);
    $validator = new class extends MediaValidator
    {
        /** @return array<string, mixed> */
        public function validate(string $filePath): array
        {
            throw new RuntimeException('Invalid media');
        }
    };

    expect(fn () => new MediaRedownloader(
        app(MediaDownloader::class),
        app(MediaStorageManager::class),
        $validator,
    )->redownload($libraryItem))->toThrow(RuntimeException::class, 'Invalid media');

    Storage::disk('public')->assertDirectoryEmpty('temp-downloads');
    Storage::disk('public')->assertExists('media/'.$oldHash.'.mp3');
    expect($mediaFile->fresh()->only(['file_path', 'file_hash']))->toBe([
        'file_path' => 'media/'.$oldHash.'.mp3',
        'file_hash' => $oldHash,
    ]);
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

it('prevents a user from redownloading a shared media file owned by another user', function () {
    Queue::fake();

    $owner = User::factory()->create();
    $user = User::factory()->create();
    $mediaFile = MediaFile::factory()->create([
        'user_id' => $owner->id,
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
        ->assertSessionHas('error', 'Cannot redownload a media file owned by another user.');

    Queue::assertNotPushed(RedownloadMediaFile::class);
    expect($libraryItem->fresh()->processing_status)->toBe(ProcessingStatusType::COMPLETED);
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
