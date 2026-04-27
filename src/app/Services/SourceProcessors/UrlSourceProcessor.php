<?php

namespace App\Services\SourceProcessors;

use App\Enums\ProcessingStatusType;
use App\Models\LibraryItem;
use App\Services\MediaProcessing\UnifiedDuplicateProcessor;
use Illuminate\Support\Facades\Log;

class UrlSourceProcessor
{
    public function __construct(
        private LibraryItemFactory $libraryItemFactory,
        private SourceStrategyInterface $strategy,
        private UnifiedDuplicateProcessor $duplicateProcessor
    ) {}

    /**
     * Handle URL source processing.
     */
    public function process(array $validated, string $sourceType, ?string $sourceUrl): array
    {
        $userId = auth()->id();

        $this->cleanupBrokenItems($sourceUrl, $userId);

        $analysis = $this->duplicateProcessor->analyzeUrlDuplicate($sourceUrl, $userId);

        if ($analysis['should_link_to_user_duplicate']) {
            $existingItem = $analysis['user_duplicate_library_item'];
            $existingItem->update([
                'title' => $validated['title'] ?? $existingItem->title,
                'description' => $validated['description'] ?? $existingItem->description,
            ]);

            return [$existingItem, $this->strategy->getSuccessMessage(true)];
        }

        if ($analysis['should_link_to_user_media_file'] || $analysis['should_link_to_global_duplicate']) {
            $mediaFile = $analysis['global_duplicate_media_file'];
            $libraryItem = $this->libraryItemFactory->createFromValidatedWithMediaFile(
                $mediaFile,
                $validated,
                $sourceType,
                $sourceUrl,
                $userId
            );

            return [$libraryItem, $this->strategy->getSuccessMessage(true)];
        }

        $libraryItem = $this->libraryItemFactory->createFromValidated($validated, $sourceType, $sourceUrl, $userId);
        $this->strategy->processNewSource($libraryItem, $sourceUrl);

        return [$libraryItem, $this->strategy->getProcessingMessage()];
    }

    /**
     * Remove broken library items for this user+URL before processing.
     * These are items from previous failed attempts that would interfere.
     */
    private function cleanupBrokenItems(string $sourceUrl, int $userId): void
    {
        $brokenItems = LibraryItem::where('source_url', $sourceUrl)
            ->where('user_id', $userId)
            ->where(function ($query) {
                $query->where('processing_status', ProcessingStatusType::FAILED)
                    ->orWhereNull('media_file_id')
                    ->orWhere('is_duplicate', true);
            })
            ->get();

        foreach ($brokenItems as $item) {
            Log::info('Cleaning up broken library item before re-upload', [
                'library_item_id' => $item->id,
                'source_url' => $sourceUrl,
                'status' => $item->processing_status?->value,
                'media_file_id' => $item->media_file_id,
                'is_duplicate' => $item->is_duplicate,
            ]);

            $item->delete();
        }
    }
}
