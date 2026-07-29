<?php

use App\Enums\ProcessingStatusType;
use App\Jobs\ProcessMediaFile;
use App\Jobs\ProcessYouTubeAudio;
use App\Models\LibraryItem;
use Illuminate\Support\Facades\Queue;

it('does not redispatch an old item retried recently by a user', function () {
    Queue::fake();

    LibraryItem::factory()->create([
        'source_url' => 'https://example.com/retry.mp3',
        'processing_status' => ProcessingStatusType::PENDING,
        'processing_started_at' => now(),
        'created_at' => now()->subHour(),
    ]);

    $this->artisan('media:retry-pending', ['--minutes' => 5])->assertSuccessful();

    Queue::assertNothingPushed();
});

it('claims a stale pending item before redispatching it', function () {
    Queue::fake();

    $item = LibraryItem::factory()->create([
        'source_url' => 'https://example.com/stale.mp3',
        'processing_status' => ProcessingStatusType::PENDING,
        'processing_started_at' => now()->subMinutes(10),
    ]);

    $this->artisan('media:retry-pending', ['--minutes' => 5])->assertSuccessful();
    $this->artisan('media:retry-pending', ['--minutes' => 5])->assertSuccessful();

    Queue::assertPushed(ProcessMediaFile::class, 1);
    expect($item->refresh()->getRawOriginal('processing_started_at'))
        ->toBeGreaterThan(now()->subMinutes(5)->toDateTimeString());
});

it('redispatches a stale pending YouTube item with its YouTube job', function () {
    Queue::fake();

    $item = LibraryItem::factory()->create([
        'source_type' => 'youtube',
        'source_url' => 'https://www.youtube.com/watch?v=test123',
        'processing_status' => ProcessingStatusType::PENDING,
        'processing_started_at' => now()->subMinutes(10),
    ]);

    $this->artisan('media:retry-pending', ['--minutes' => 5])->assertSuccessful();

    Queue::assertPushed(ProcessYouTubeAudio::class, fn (ProcessYouTubeAudio $job) => $job->getLibraryItemId() === $item->id);
    Queue::assertNotPushed(ProcessMediaFile::class);
});
