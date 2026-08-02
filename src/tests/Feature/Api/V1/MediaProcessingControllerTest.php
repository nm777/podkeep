<?php

use App\Enums\ProcessingStatusType;
use App\Jobs\ProcessMediaFile;
use App\Jobs\RedownloadMediaFile;
use App\Models\Chapter;
use App\Models\LibraryItem;
use App\Models\MediaFile;
use App\Models\User;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Storage;

describe('retry processing', function () {
    it('retries a failed library item', function () {
        Queue::fake();

        $user = User::factory()->create();
        $token = $user->createToken('test')->plainTextToken;

        $item = LibraryItem::factory()->create([
            'user_id' => $user->id,
            'processing_status' => ProcessingStatusType::FAILED,
            'source_type' => 'url',
            'source_url' => 'https://example.com/episode.mp3',
        ]);

        $response = $this->withHeader('Authorization', 'Bearer '.$token)
            ->postJson('/api/v1/library/'.$item->id.'/retry');

        $response->assertOk();
        $response->assertJsonPath('data.processing_status', 'pending');
        Queue::assertPushed(ProcessMediaFile::class);
    });

    it('prevents retrying a non-failed item', function () {
        $user = User::factory()->create();
        $token = $user->createToken('test')->plainTextToken;

        $item = LibraryItem::factory()->create([
            'user_id' => $user->id,
            'processing_status' => ProcessingStatusType::COMPLETED,
        ]);

        $response = $this->withHeader('Authorization', 'Bearer '.$token)
            ->postJson('/api/v1/library/'.$item->id.'/retry');

        expect($response->status())->toBeIn([400, 422]);
        $response->assertJsonPath('message', 'Only failed items can be retried.');
    });

    it('prevents retrying another users item', function () {
        Queue::fake();

        $userA = User::factory()->create();
        $tokenA = $userA->createToken('test')->plainTextToken;

        $userB = User::factory()->create();
        $itemB = LibraryItem::factory()->create([
            'user_id' => $userB->id,
            'processing_status' => ProcessingStatusType::FAILED,
            'source_type' => 'url',
            'source_url' => 'https://example.com/episode.mp3',
        ]);

        $response = $this->withHeader('Authorization', 'Bearer '.$tokenA)
            ->postJson('/api/v1/library/'.$itemB->id.'/retry');

        $response->assertNotFound();
        Queue::assertNotPushed(ProcessMediaFile::class);
    });

    it('rejects unauthenticated retry', function () {
        $response = $this->withHeader('Accept', 'application/json')
            ->postJson('/api/v1/library/1/retry');

        $response->assertUnauthorized();
    });
});

describe('redownload', function () {
    it('clears annotations when changed content is redownloaded', function () {
        Storage::fake('public');
        Http::fake([
            'https://example.com/new-episode.mp3' => Http::response('RIFFnew audio content', 200),
        ]);

        $user = User::factory()->create();
        $token = $user->createToken('test')->plainTextToken;
        $oldContent = 'RIFFold audio content';
        $oldHash = hash('sha256', $oldContent);
        Storage::disk('public')->put('media/'.$oldHash.'.mp3', $oldContent);

        $mediaFile = MediaFile::factory()->create([
            'user_id' => $user->id,
            'file_path' => 'media/'.$oldHash.'.mp3',
            'file_hash' => $oldHash,
            'source_url' => 'https://example.com/new-episode.mp3',
            'transcript' => [['start' => 0, 'end' => 5, 'text' => 'cached']],
            'chapter_generation_status' => 'failed',
            'chapter_proposal' => [['start_time' => 0, 'title' => 'Intro']],
            'chapter_proposal_for_hash' => $oldHash,
            'chapter_generation_error' => 'Generation failed',
        ]);
        Chapter::factory()->create(['media_file_id' => $mediaFile->id]);
        $item = LibraryItem::factory()->create([
            'user_id' => $user->id,
            'media_file_id' => $mediaFile->id,
            'source_type' => 'url',
        ]);

        $this->withHeader('Authorization', 'Bearer '.$token)
            ->postJson('/api/v1/library/'.$item->id.'/redownload')
            ->assertOk();

        $this->artisan('queue:work --once')->assertExitCode(0);

        $mediaFile->refresh();
        expect($mediaFile->transcript)->toBeNull();
        expect($mediaFile->chapter_generation_status)->toBeNull();
        expect($mediaFile->chapter_proposal)->toBeNull();
        expect($mediaFile->chapter_proposal_for_hash)->toBeNull();
        expect($mediaFile->chapter_generation_error)->toBeNull();
        expect($mediaFile->chapters)->toHaveCount(0);
    });

    it('redownloads media from source URL', function () {
        Queue::fake();

        $user = User::factory()->create();
        $token = $user->createToken('test')->plainTextToken;

        $mediaFile = MediaFile::factory()->create([
            'user_id' => $user->id,
            'source_url' => 'https://example.com/episode.mp3',
        ]);
        $item = LibraryItem::factory()->create([
            'user_id' => $user->id,
            'media_file_id' => $mediaFile->id,
            'processing_status' => ProcessingStatusType::COMPLETED,
            'source_type' => 'url',
            'source_url' => 'https://example.com/episode.mp3',
        ]);

        $response = $this->withHeader('Authorization', 'Bearer '.$token)
            ->postJson('/api/v1/library/'.$item->id.'/redownload');

        $response->assertOk();
        $response->assertJsonPath('data.processing_status', 'processing');
        Queue::assertPushed(RedownloadMediaFile::class);
    });

    it('rejects redownloading an item already being processed', function () {
        Queue::fake();

        $user = User::factory()->create();
        $token = $user->createToken('test')->plainTextToken;
        $mediaFile = MediaFile::factory()->create([
            'user_id' => $user->id,
            'source_url' => 'https://example.com/episode.mp3',
        ]);
        $item = LibraryItem::factory()->create([
            'user_id' => $user->id,
            'media_file_id' => $mediaFile->id,
            'processing_status' => ProcessingStatusType::PROCESSING,
        ]);

        $this->withHeader('Authorization', 'Bearer '.$token)
            ->postJson('/api/v1/library/'.$item->id.'/redownload')
            ->assertUnprocessable()
            ->assertJsonPath('message', 'This media file is already being processed.');

        Queue::assertNotPushed(RedownloadMediaFile::class);
    });

    it('atomically claims an item before dispatching an API redownload job', function () {
        Queue::fake();

        $user = User::factory()->create();
        $token = $user->createToken('test')->plainTextToken;
        $mediaFile = MediaFile::factory()->create([
            'user_id' => $user->id,
            'source_url' => 'https://example.com/episode.mp3',
        ]);
        $item = LibraryItem::factory()->create([
            'user_id' => $user->id,
            'media_file_id' => $mediaFile->id,
            'source_type' => 'url',
        ]);

        $this->withHeader('Authorization', 'Bearer '.$token)
            ->postJson('/api/v1/library/'.$item->id.'/redownload')
            ->assertOk();
        $this->withHeader('Authorization', 'Bearer '.$token)
            ->postJson('/api/v1/library/'.$item->id.'/redownload')
            ->assertUnprocessable()
            ->assertJsonPath('message', 'This media file is already being processed.');

        Queue::assertPushed(RedownloadMediaFile::class, 1);
    });

    it('prevents redownload without source url', function () {
        $user = User::factory()->create();
        $token = $user->createToken('test')->plainTextToken;

        $mediaFile = MediaFile::factory()->create([
            'user_id' => $user->id,
            'source_url' => null,
        ]);
        $item = LibraryItem::factory()->create([
            'user_id' => $user->id,
            'media_file_id' => $mediaFile->id,
            'processing_status' => ProcessingStatusType::COMPLETED,
            'source_type' => 'upload',
        ]);

        $response = $this->withHeader('Authorization', 'Bearer '.$token)
            ->postJson('/api/v1/library/'.$item->id.'/redownload');

        expect($response->status())->toBeIn([400, 422]);
        $response->assertJsonPath('message', 'Cannot redownload: no source URL available for this media file.');
    });

    it('prevents redownloading another users item', function () {
        Queue::fake();

        $userA = User::factory()->create();
        $tokenA = $userA->createToken('test')->plainTextToken;

        $userB = User::factory()->create();
        $mediaFile = MediaFile::factory()->create([
            'user_id' => $userB->id,
            'source_url' => 'https://example.com/episode.mp3',
        ]);
        $itemB = LibraryItem::factory()->create([
            'user_id' => $userB->id,
            'media_file_id' => $mediaFile->id,
            'processing_status' => ProcessingStatusType::COMPLETED,
            'source_type' => 'url',
        ]);

        $response = $this->withHeader('Authorization', 'Bearer '.$tokenA)
            ->postJson('/api/v1/library/'.$itemB->id.'/redownload');

        $response->assertNotFound();
        Queue::assertNotPushed(RedownloadMediaFile::class);
    });

    it('prevents redownloading a shared media file owned by another user', function () {
        Queue::fake();

        $owner = User::factory()->create();
        $user = User::factory()->create();
        $token = $user->createToken('test')->plainTextToken;
        $mediaFile = MediaFile::factory()->create([
            'user_id' => $owner->id,
            'source_url' => 'https://example.com/episode.mp3',
        ]);
        $item = LibraryItem::factory()->create([
            'user_id' => $user->id,
            'media_file_id' => $mediaFile->id,
            'processing_status' => ProcessingStatusType::COMPLETED,
            'source_type' => 'url',
        ]);

        $response = $this->withHeader('Authorization', 'Bearer '.$token)
            ->postJson('/api/v1/library/'.$item->id.'/redownload');

        $response->assertUnprocessable();
        $response->assertJsonPath('message', 'Cannot redownload a media file owned by another user.');
        Queue::assertNotPushed(RedownloadMediaFile::class);
        expect($item->fresh()->processing_status)->toBe(ProcessingStatusType::COMPLETED);
    });
});
