<?php

use App\Enums\ProcessingStatusType;
use App\Jobs\ProcessYouTubeAudio;
use App\Jobs\RedownloadMediaFile;
use App\Jobs\TranscribeMediaFile;
use App\Models\Chapter;
use App\Models\Feed;
use App\Models\FeedItem;
use App\Models\LibraryItem;
use App\Models\MediaFile;
use App\Models\User;
use App\Services\MediaProcessing\MediaDownloader;
use App\Services\MediaProcessing\MediaRedownloader;
use App\Services\MediaProcessing\MediaStorageManager;
use App\Services\MediaProcessing\MediaValidator;
use App\Services\Transcription\WhisperClient;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Storage;
use Illuminate\Queue\Middleware\WithoutOverlapping;

use function Pest\Laravel\actingAs;

beforeEach(function () {
    Storage::fake('media');

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

    Storage::disk('media')->put('media/'.$fileHash.'.mp3', $fileContent);

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

it('rejects redownloading an item already being processed', function () {
    Queue::fake();

    $user = User::factory()->create();
    $mediaFile = MediaFile::factory()->create([
        'user_id' => $user->id,
        'source_url' => 'https://example.com/audio.mp3',
    ]);
    $libraryItem = LibraryItem::factory()->create([
        'user_id' => $user->id,
        'media_file_id' => $mediaFile->id,
        'processing_status' => ProcessingStatusType::PROCESSING,
    ]);

    actingAs($user)
        ->post("/library/{$libraryItem->id}/redownload")
        ->assertRedirect()
        ->assertSessionHas('error', 'This media file is already being processed.');

    Queue::assertNotPushed(RedownloadMediaFile::class);
});

it('atomically claims an item before dispatching a redownload job', function () {
    Queue::fake();

    $user = User::factory()->create();
    $mediaFile = MediaFile::factory()->create([
        'user_id' => $user->id,
        'source_url' => 'https://example.com/audio.mp3',
    ]);
    $libraryItem = LibraryItem::factory()->create([
        'user_id' => $user->id,
        'media_file_id' => $mediaFile->id,
        'source_type' => 'url',
    ]);

    actingAs($user)->post("/library/{$libraryItem->id}/redownload")->assertRedirect();
    actingAs($user)->post("/library/{$libraryItem->id}/redownload")
        ->assertRedirect()
        ->assertSessionHas('error', 'This media file is already being processed.');

    Queue::assertPushed(RedownloadMediaFile::class, 1);
});

it('prevents overlapping redownload jobs for the same media file', function () {
    $mediaFile = MediaFile::factory()->create();
    $libraryItem = LibraryItem::factory()->create(['media_file_id' => $mediaFile->id]);

    $middleware = (new RedownloadMediaFile($libraryItem))->middleware();

    expect($middleware)->toHaveCount(1)
        ->and($middleware[0])->toBeInstanceOf(WithoutOverlapping::class)
        ->and($middleware[0]->key)->toBe('media-file-'.$mediaFile->id)
        ->and($middleware[0]->expiresAfter)->toBe(360)
        ->and($middleware[0]->releaseAfter)->toBeNull();
});

it('does not redispatch already claimed missing media', function () {
    Queue::fake();

    $user = User::factory()->create();
    $mediaFile = MediaFile::factory()->create([
        'user_id' => $user->id,
        'source_url' => 'https://example.com/audio.mp3',
    ]);
    LibraryItem::factory()->create([
        'user_id' => $user->id,
        'media_file_id' => $mediaFile->id,
        'processing_status' => ProcessingStatusType::PROCESSING,
    ]);

    $this->artisan('media:redownload-missing')->assertExitCode(0);

    Queue::assertNotPushed(RedownloadMediaFile::class);
});

it('allows user to redownload their own media file', function () {
    $user = User::factory()->create();

    $fileContent = 'RIFFfake audio content';
    $fileHash = hash('sha256', $fileContent);

    Storage::disk('media')->put('media/'.$fileHash.'.mp3', $fileContent);

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

    Storage::disk('media')->put('media/'.$oldHash.'.mp3', $oldContent);

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

    Storage::disk('media')->assertMissing('media/'.$oldHash.'.mp3');
    Storage::disk('media')->assertExists('media/'.$newHash.'.mp3');
});

it('prevents an old transcription job from checkpointing after an in-place redownload', function () {
    $user = User::factory()->create();
    $oldContent = 'RIFFfake audio content';
    $oldHash = hash('sha256', $oldContent);

    Storage::disk('media')->put('media/'.$oldHash.'.mp3', $oldContent);

    $mediaFile = MediaFile::factory()->create([
        'user_id' => $user->id,
        'file_path' => 'media/'.$oldHash.'.mp3',
        'file_hash' => $oldHash,
        'source_url' => 'https://example.com/new-audio.mp3',
        'duration' => 600,
    ]);
    $libraryItem = LibraryItem::factory()->create([
        'user_id' => $user->id,
        'media_file_id' => $mediaFile->id,
    ]);
    $whisper = new class($libraryItem) extends WhisperClient
    {
        public function __construct(private LibraryItem $libraryItem) {}

        public function chunk(string $source, int $chunkSeconds): array
        {
            return ['dir' => '/tmp/fake', 'segments' => [['path' => '/tmp/c0.wav', 'offset' => 0]]];
        }

        public function transcribeFile(string $wavPath): array
        {
            app(MediaRedownloader::class)->redownload($this->libraryItem);

            return [['start' => 0, 'end' => 5, 'text' => 'stale checkpoint']];
        }

        public function cleanupChunks(string $dir): void {}
    };

    (new TranscribeMediaFile($mediaFile))->handle($whisper);

    expect($mediaFile->fresh()->only(['chapter_generation_version', 'transcript', 'chapter_generation_status']))->toBe([
        'chapter_generation_version' => 1,
        'transcript' => null,
        'chapter_generation_status' => null,
    ]);
});

it('evicts cached RSS when an in-place redownload changes the enclosure path', function () {
    $user = User::factory()->create();
    $oldContent = 'RIFFfake audio content';
    $oldHash = hash('sha256', $oldContent);
    $newHash = hash('sha256', 'RIFFnew audio content');

    Storage::disk('media')->put('media/'.$oldHash.'.mp3', $oldContent);

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
    $feed = Feed::factory()->create(['user_id' => $user->id, 'is_public' => true]);
    FeedItem::factory()->create(['feed_id' => $feed->id, 'library_item_id' => $libraryItem->id]);

    $this->get("/rss/{$feed->user_guid}/{$feed->slug}")->assertSuccessful();
    expect(Cache::has("rss.{$feed->id}"))->toBeTrue();

    app(MediaRedownloader::class)->redownload($libraryItem);

    expect($mediaFile->fresh()->file_path)->toBe('media/'.$newHash.'.mp3')
        ->and(Cache::has("rss.{$feed->id}"))->toBeFalse();
});

it('relinks a sole-reference redownload to an existing media hash', function () {
    $user = User::factory()->create();
    $oldContent = 'RIFFfake audio content';
    $oldHash = hash('sha256', $oldContent);
    $existingContent = 'RIFFnew audio content';
    $existingHash = hash('sha256', $existingContent);

    Storage::disk('media')->put('media/'.$oldHash.'.mp3', $oldContent);
    Storage::disk('media')->put('media/'.$existingHash.'.mp3', $existingContent);

    $oldMediaFile = MediaFile::factory()->create([
        'user_id' => $user->id,
        'file_path' => 'media/'.$oldHash.'.mp3',
        'file_hash' => $oldHash,
        'source_url' => 'https://example.com/new-audio.mp3',
    ]);
    $existingMediaFile = MediaFile::factory()->create([
        'file_path' => 'media/'.$existingHash.'.mp3',
        'file_hash' => $existingHash,
        'transcript' => [['start' => 0, 'end' => 5, 'text' => 'preserved']],
    ]);
    $libraryItem = LibraryItem::factory()->create([
        'user_id' => $user->id,
        'media_file_id' => $oldMediaFile->id,
    ]);
    $feed = Feed::factory()->create(['user_id' => $user->id, 'is_public' => true]);
    FeedItem::factory()->create(['feed_id' => $feed->id, 'library_item_id' => $libraryItem->id]);

    $this->get("/rss/{$feed->user_guid}/{$feed->slug}")->assertSuccessful();
    expect(Cache::has("rss.{$feed->id}"))->toBeTrue();

    app(MediaRedownloader::class)->redownload($libraryItem);

    expect($libraryItem->fresh()->media_file_id)->toBe($existingMediaFile->id)
        ->and($existingMediaFile->fresh()->transcript)->toBe([['start' => 0, 'end' => 5, 'text' => 'preserved']])
        ->and(Cache::has("rss.{$feed->id}"))->toBeFalse();
    $this->assertModelMissing($oldMediaFile);
    Storage::disk('media')->assertMissing('media/'.$oldHash.'.mp3');
    Storage::disk('media')->assertExists('media/'.$existingHash.'.mp3');
});

it('relinks only the requested item when redownloading shared media with changed content', function () {
    $owner = User::factory()->create();
    $otherUser = User::factory()->create();
    $oldContent = 'RIFFfake audio content';
    $oldHash = hash('sha256', $oldContent);
    $newHash = hash('sha256', 'RIFFnew audio content');

    Storage::disk('media')->put('media/'.$oldHash.'.mp3', $oldContent);

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

    Storage::disk('media')->assertExists('media/'.$oldHash.'.mp3');
    Storage::disk('media')->assertExists('media/'.$newHash.'.mp3');
});

it('rechecks references after a concurrent duplicate link before redownloading', function () {
    $owner = User::factory()->create();
    $otherUser = User::factory()->create();
    $oldContent = 'RIFFfake audio content';
    $oldHash = hash('sha256', $oldContent);
    $newHash = hash('sha256', 'RIFFnew audio content');

    Storage::disk('media')->put('media/'.$oldHash.'.mp3', $oldContent);

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
    ]);
    $otherItem = LibraryItem::factory()->create([
        'user_id' => $otherUser->id,
        'media_file_id' => null,
    ]);
    $storageManager = new class($otherItem, $mediaFile) extends MediaStorageManager
    {
        public function __construct(private LibraryItem $otherItem, private MediaFile $mediaFile) {}

        /** @return array{file_path: string, file_hash: string, filesize: int, source_url: string|null} */
        public function moveTempFile(string $tempPath, ?string $sourceUrl = null): array
        {
            $this->otherItem->linkMediaFile($this->mediaFile);

            return parent::moveTempFile($tempPath, $sourceUrl);
        }
    };

    (new MediaRedownloader(
        app(MediaDownloader::class),
        $storageManager,
        app(MediaValidator::class),
    ))->redownload($requestedItem);

    expect($otherItem->fresh()->media_file_id)->toBe($mediaFile->id);
    expect($mediaFile->fresh()->only(['file_hash', 'file_path', 'transcript']))->toBe([
        'file_hash' => $oldHash,
        'file_path' => 'media/'.$oldHash.'.mp3',
        'transcript' => [['start' => 0, 'end' => 5, 'text' => 'cached']],
    ]);
    expect($requestedItem->fresh()->mediaFile->file_hash)->toBe($newHash);
});

it('removes a newly moved file when redownload persistence fails', function () {
    $user = User::factory()->create();
    $oldContent = 'RIFFfake audio content';
    $oldHash = hash('sha256', $oldContent);
    $newContent = 'RIFFnew audio content';
    $newHash = hash('sha256', $newContent);

    Storage::disk('media')->put('media/'.$oldHash.'.mp3', $oldContent);

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

    Storage::disk('media')->assertExists('media/'.$oldHash.'.mp3');
    Storage::disk('media')->assertMissing('media/'.$newHash.'.mp3');
    expect($mediaFile->fresh()->only(['file_path', 'file_hash']))->toBe([
        'file_path' => 'media/'.$oldHash.'.mp3',
        'file_hash' => $oldHash,
    ]);
});

it('removes the downloaded temp file when redownload validation fails', function () {
    $user = User::factory()->create();
    $oldContent = 'RIFFfake audio content';
    $oldHash = hash('sha256', $oldContent);

    Storage::disk('media')->put('media/'.$oldHash.'.mp3', $oldContent);

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

    Storage::disk('media')->assertDirectoryEmpty('temp-downloads');
    Storage::disk('media')->assertExists('media/'.$oldHash.'.mp3');
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

    Storage::disk('media')->assertMissing('media/'.$fileHash.'.mp3');

    actingAs($user)
        ->post("/library/{$libraryItem->id}/redownload")
        ->assertRedirect();

    $this->artisan('queue:work --once')->assertExitCode(0);

    Storage::disk('media')->assertExists('media/'.$fileHash.'.mp3');
    $storedContent = Storage::disk('media')->get('media/'.$fileHash.'.mp3');
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

    Storage::disk('media')->put('media/'.$fileHash.'.mp3', $fileContent);

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

    Storage::disk('media')->put('media/'.$fileHash.'.mp3', $fileContent);

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
    expect($libraryItem->processing_error)->toBe('Media redownload failed.');
});

it('rethrows unexpected redownload errors and records a safe terminal failure', function () {
    $user = User::factory()->create();
    $mediaFile = MediaFile::factory()->create(['user_id' => $user->id, 'source_url' => 'https://example.com/audio.mp3']);
    $libraryItem = LibraryItem::factory()->create([
        'user_id' => $user->id,
        'media_file_id' => $mediaFile->id,
        'processing_status' => ProcessingStatusType::PROCESSING,
    ]);
    $redownloader = new class extends MediaRedownloader
    {
        public int $calls = 0;

        public function __construct() {}

        /** @return array{success: true, file_existed: bool, hash_changed: bool, old_hash: string, new_hash: string} */
        public function redownload(LibraryItem $libraryItem): array
        {
            $this->calls++;

            throw new RuntimeException('Database password: secret');
        }
    };
    $job = new RedownloadMediaFile($libraryItem);

    expect(fn () => $job->handle($redownloader))->toThrow(RuntimeException::class);
    expect($redownloader->calls)->toBe(1);

    $job->failed(new RuntimeException('Database password: secret'));

    expect($libraryItem->fresh()->only(['processing_status', 'processing_error']))->toBe([
        'processing_status' => ProcessingStatusType::FAILED,
        'processing_error' => 'Media redownload failed.',
    ]);
});

it('does not let a stale redownload failure overwrite a completed item', function () {
    $libraryItem = LibraryItem::factory()->create([
        'processing_status' => ProcessingStatusType::COMPLETED,
    ]);

    (new RedownloadMediaFile($libraryItem))->failed(new RuntimeException('Stale failure'));

    expect($libraryItem->fresh()->processing_status)->toBe(ProcessingStatusType::COMPLETED);
});

it('logs a generic error when redownload exception contains credentials', function () {
    $user = User::factory()->create();
    $url = 'https://user:secret@example.com/audio.mp3?token=secret';
    $mediaFile = MediaFile::factory()->create(['user_id' => $user->id, 'source_url' => $url]);
    $libraryItem = LibraryItem::factory()->create(['user_id' => $user->id, 'media_file_id' => $mediaFile->id]);
    $downloader = new class($url) extends MediaDownloader
    {
        public bool $called = false;

        public function __construct(private string $url) {}

        public function downloadFromUrl(string $url): string
        {
            $this->called = true;

            throw new RuntimeException("Download failed: {$this->url}");
        }
    };

    Log::shouldReceive('error')->once()->with('Media redownload failed', [
        'library_item_id' => $libraryItem->id,
        'media_file_id' => $mediaFile->id,
        'error_code' => 'media_redownload_failed',
        'message' => 'Media redownload failed.',
    ]);

    expect(fn () => new MediaRedownloader(
        $downloader,
        app(MediaStorageManager::class),
        app(MediaValidator::class),
    )->redownload($libraryItem))->toThrow(RuntimeException::class, "Download failed: {$url}");

    $this->assertTrue($downloader->called);
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

    Storage::disk('media')->assertExists('media/'.$fileHash.'.mp3');
});
