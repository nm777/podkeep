<?php

use App\Jobs\AddLibraryItemToFeedsJob;
use App\Models\Feed;
use App\Models\LibraryItem;
use App\Models\User;
use App\Services\MediaProcessing\UnifiedDuplicateProcessor;
use App\Services\SourceProcessors\FileUploadProcessor;
use App\Services\SourceProcessors\LibraryItemFactory;
use App\Services\SourceProcessors\UploadStrategy;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Storage;

describe('FileUploadProcessor', function () {
    it('can be instantiated with dependencies', function () {
        $duplicateProcessor = Mockery::mock(UnifiedDuplicateProcessor::class);
        $libraryItemFactory = new LibraryItemFactory;
        $strategy = new UploadStrategy;

        $processor = new FileUploadProcessor($duplicateProcessor, $libraryItemFactory, $strategy);

        expect($processor)->toBeInstanceOf(FileUploadProcessor::class);
    });

    it('delegates processing message to strategy', function () {
        $duplicateProcessor = Mockery::mock(UnifiedDuplicateProcessor::class);
        $libraryItemFactory = new LibraryItemFactory;
        $strategy = new UploadStrategy;

        expect($strategy->getProcessingMessage())->toBe('Media file uploaded successfully. Processing...');
    });

    it('delegates success messages to strategy', function () {
        $strategy = new UploadStrategy;

        $duplicateMessage = $strategy->getSuccessMessage(true);
        $newFileMessage = $strategy->getSuccessMessage(false);

        expect($duplicateMessage)->toContain('Duplicate file detected');
        expect($duplicateMessage)->toContain(config('constants.duplicate.cleanup_delay_minutes').' minutes.');
        expect($newFileMessage)->toBe('Media file uploaded successfully. Processing...');
    });

    it('only dispatches the feed job for the final uploaded item', function () {
        Storage::fake('public');
        Queue::fake();

        $user = User::factory()->create();
        $feed = Feed::factory()->create(['user_id' => $user->id]);

        $this->actingAs($user)->post(route('library.store'), [
            'title' => 'Selected Feed Upload',
            'file' => UploadedFile::fake()->createWithContent('upload.mp3', 'new audio content'),
            'feed_ids' => [$feed->id],
        ])->assertRedirect('/library');

        $libraryItem = LibraryItem::where('title', 'Selected Feed Upload')->sole();

        Queue::assertPushedTimes(AddLibraryItemToFeedsJob::class, 1);
        Queue::assertPushed(AddLibraryItemToFeedsJob::class, function (AddLibraryItemToFeedsJob $job) use ($feed, $libraryItem) {
            return $job->libraryItem->is($libraryItem)
                && $job->feedIds === [$feed->id]
                && LibraryItem::find($job->libraryItem->id) !== null;
        });
    });
});
