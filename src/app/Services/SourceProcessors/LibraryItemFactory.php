<?php

namespace App\Services\SourceProcessors;

use App\Enums\ProcessingStatusType;
use App\Jobs\AddLibraryItemToFeedsJob;
use App\Models\LibraryItem;
use App\Models\MediaFile;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;

class LibraryItemFactory
{
    /**
     * Create library item from validated data.
     */
    public function createFromValidated(array $validated, string $sourceType, ?string $sourceUrl = null, ?int $userId = null): LibraryItem
    {
        $attributes = [
            'title' => $validated['title'],
            'description' => $validated['description'] ?? null,
            'published_at' => $validated['published_at'] ?? null,
            'user_id' => $userId ?? auth()->id(),
            'source_type' => $sourceType,
            'media_type' => $validated['media_type'] ?? 'audio',
            'auto_generate_chapters' => $validated['auto_generate_chapters'] ?? false,
            'source_url' => $sourceUrl,
            'processing_status' => ProcessingStatusType::PENDING,
        ];

        if ($sourceUrl) {
            $libraryItem = LibraryItem::findActiveBySourceUrlForUser($sourceUrl, $attributes['user_id']);

            if ($libraryItem) {
                $this->dispatchFeedJob($libraryItem, $validated);

                return $libraryItem;
            }

            try {
                $libraryItem = LibraryItem::create($attributes);
            } catch (QueryException $exception) {
                $libraryItem = LibraryItem::findActiveBySourceUrlForUser($sourceUrl, $attributes['user_id']);

                if (! $libraryItem) {
                    throw $exception;
                }

                $this->dispatchFeedJob($libraryItem, $validated);

                return $libraryItem;
            }
        } else {
            $libraryItem = LibraryItem::create($attributes);
        }

        $this->dispatchFeedJob($libraryItem, $validated);

        return $libraryItem;
    }

    /**
     * Create library item from validated data with media file data.
     */
    public function createFromValidatedWithMediaData(array $validated, string $sourceType, array $mediaFileData, ?int $userId = null): LibraryItem
    {
        $libraryItem = LibraryItem::create([
            'title' => $validated['title'],
            'description' => $validated['description'] ?? null,
            'published_at' => $validated['published_at'] ?? null,
            'user_id' => $userId ?? auth()->id(),
            'source_type' => $sourceType,
            'media_type' => $validated['media_type'] ?? 'audio',
            'auto_generate_chapters' => $validated['auto_generate_chapters'] ?? false,
            'processing_status' => ProcessingStatusType::PENDING,
        ] + $mediaFileData);

        $this->dispatchFeedJob($libraryItem, $validated);

        return $libraryItem;
    }

    /**
     * Update library item with validated data while preserving existing media file relationship.
     */
    public function createFromValidatedWithMediaFile($mediaFile, array $validated, string $sourceType, ?string $sourceUrl = null, ?int $userId = null): LibraryItem
    {
        $currentUserId = $userId ?? auth()->id();
        $attributes = [
            'title' => $validated['title'],
            'description' => $validated['description'] ?? null,
            'published_at' => $validated['published_at'] ?? null,
            'user_id' => $currentUserId,
            'source_type' => $sourceType,
            'media_type' => $validated['media_type'] ?? 'audio',
            'auto_generate_chapters' => $validated['auto_generate_chapters'] ?? false,
            'source_url' => $sourceUrl,
            'media_file_id' => $mediaFile->id,
            'is_duplicate' => false,
            'processing_status' => ProcessingStatusType::COMPLETED,
            'processing_completed_at' => now(),
        ];

        $libraryItem = DB::transaction(function () use ($mediaFile, $attributes): LibraryItem {
            MediaFile::query()->lockForUpdate()->findOrFail($mediaFile->id);

            return LibraryItem::create($attributes);
        });

        $this->dispatchFeedJob($libraryItem, $validated);

        return $libraryItem;
    }

    /**
     * Dispatch job to add library item to feeds after processing completes.
     */
    public function dispatchFeedJob(LibraryItem $libraryItem, array $validated): void
    {
        if (empty($validated['feed_ids'])) {
            return;
        }

        // Only dispatch job for items that need processing
        if ($libraryItem->isPending() || $libraryItem->isProcessing()) {
            AddLibraryItemToFeedsJob::dispatch($libraryItem, $validated['feed_ids'])
                ->delay(now()->addSeconds(config('constants.processing.start_delay_seconds')));
        } else {
            // For completed items, add to feeds immediately
            AddLibraryItemToFeedsJob::dispatchSync($libraryItem, $validated['feed_ids']);
        }
    }
}
