<?php

use App\Enums\ProcessingStatusType;
use App\Jobs\ProcessMediaFile;
use App\Jobs\RedownloadMediaFile;
use App\Models\LibraryItem;
use App\Models\MediaFile;
use App\Models\User;
use Illuminate\Support\Facades\Queue;

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
});
