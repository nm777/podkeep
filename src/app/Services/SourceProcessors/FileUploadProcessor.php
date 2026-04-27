<?php

namespace App\Services\SourceProcessors;

use App\Http\Requests\LibraryItemRequest;
use App\Jobs\CleanupDuplicateLibraryItem;
use App\Jobs\ProcessMediaFile;
use App\Services\MediaProcessing\UnifiedDuplicateProcessor;
use Illuminate\Support\Facades\Storage;

class FileUploadProcessor
{
    public function __construct(
        private UnifiedDuplicateProcessor $duplicateProcessor,
        private LibraryItemFactory $libraryItemFactory,
        private UploadStrategy $strategy
    ) {}

    public function process(LibraryItemRequest $request, array $validated, string $sourceType): array
    {
        $file = $request->file('file');
        $tempPath = $file->store('temp-uploads', 'public');
        $userId = auth()->id();

        $tempLibraryItem = $this->libraryItemFactory->createFromValidated($validated, $sourceType, null, $userId);

        $duplicateResult = $this->duplicateProcessor->processFileDuplicate($tempLibraryItem, $tempPath);

        if ($duplicateResult['media_file']) {
            Storage::disk('public')->delete($tempPath);

            $tempLibraryItem->delete();

            $libraryItem = $this->libraryItemFactory->createFromValidatedWithMediaFile(
                $duplicateResult['media_file'],
                $validated,
                $sourceType,
                null,
                $userId
            );

            if ($duplicateResult['is_duplicate']) {
                $libraryItem->update([
                    'is_duplicate' => true,
                    'duplicate_detected_at' => now(),
                ]);

                CleanupDuplicateLibraryItem::dispatch($libraryItem)->delay(
                    now()->addMinutes(config('constants.duplicate.cleanup_delay_minutes'))
                );
            }

            return [$libraryItem, $this->strategy->getSuccessMessage($duplicateResult['is_duplicate'])];
        }

        $tempLibraryItem->delete();

        $fullTempPath = Storage::disk('public')->path($tempPath);
        $fileHash = hash_file('sha256', $fullTempPath);

        $libraryItem = $this->libraryItemFactory->createFromValidatedWithMediaData($validated, $sourceType, [
            'file_path' => $tempPath,
            'file_hash' => $fileHash,
            'mime_type' => $file->getMimeType(),
            'filesize' => $file->getSize(),
        ], $userId);

        ProcessMediaFile::dispatch($libraryItem, null, $tempPath);

        return [$libraryItem, $this->strategy->getProcessingMessage()];
    }
}
