<?php

namespace App\Services\SourceProcessors;

use App\Models\LibraryItem;
use Illuminate\Foundation\Http\FormRequest;

class UnifiedSourceProcessor
{
    public function __construct(
        private FileUploadProcessor $fileUploadProcessor,
        private UrlSourceProcessor $urlSourceProcessor,
        private SourceStrategyInterface $strategy
    ) {}

    /**
     * Process source using unified logic with strategy pattern.
     *
     * @param  array<string, mixed>  $validated
     * @return array{0: LibraryItem, 1: string}
     */
    public function process(FormRequest $request, array $validated, string $sourceType, ?string $sourceUrl): array
    {
        // Validate source using strategy
        $this->strategy->validate($sourceUrl);

        // Handle file upload for new items
        if ($sourceType === 'upload') {
            return $this->fileUploadProcessor->process($request, $validated, $sourceType);
        }

        // Handle URL sources (YouTube, regular URL)
        return $this->urlSourceProcessor->process($validated, $sourceType, $sourceUrl);
    }

    /**
     * Retry processing a failed library item.
     */
    public function retry(LibraryItem $libraryItem): void
    {
        $this->strategy->processNewSource($libraryItem, $libraryItem->source_url);
    }
}
