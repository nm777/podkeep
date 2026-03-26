<?php

namespace App\Services\SourceProcessors;

use App\Models\LibraryItem;

interface SourceStrategyInterface
{
    /**
     * Validate the source based on strategy requirements.
     */
    public function validate(?string $sourceUrl): void;

    /**
     * Process a new source using strategy-specific logic.
     */
    public function processNewSource(LibraryItem $libraryItem, ?string $sourceUrl): void;

    /**
     * Get success message based on whether it was a duplicate.
     */
    public function getSuccessMessage(bool $isDuplicate): string;

    /**
     * Get processing message for new sources.
     */
    public function getProcessingMessage(): string;
}
