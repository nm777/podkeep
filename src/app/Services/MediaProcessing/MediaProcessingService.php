<?php

namespace App\Services\MediaProcessing;

use App\Enums\ProcessingStatusType;
use App\Jobs\SegmentTranscriptIntoChapters;
use App\Jobs\TranscribeMediaFile;
use App\Models\LibraryItem;
use App\Models\MediaFile;
use App\Services\DuplicateDetectionService;
use App\Services\MediaFileRetirementService;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class MediaProcessingService
{
    public function __construct(
        private MediaDownloader $downloader,
        private MediaValidator $validator,
        private MediaStorageManager $storageManager,
        private UnifiedDuplicateProcessor $duplicateProcessor,
        private VideoToAudioConverter $videoToAudioConverter
    ) {}

    /**
     * Process media file from URL.
     */
    public function processFromUrl(LibraryItem $libraryItem, string $sourceUrl, ?string $mediaType = null): array
    {
        try {
            $this->markAsProcessing($libraryItem);

            $duplicateResult = $this->duplicateProcessor->processUrlDuplicate($libraryItem, $sourceUrl);
            if ($duplicateResult['media_file']) {
                return $duplicateResult;
            }

            $sourceTempPath = $this->downloader->downloadFromUrl($sourceUrl);
            $tempPath = $sourceTempPath;

            try {
                // If user wants audio from a video source, convert it
                if ($mediaType === 'audio') {
                    $mimeType = Storage::disk('public')->mimeType($tempPath);
                    if (str_starts_with($mimeType, 'video/')) {
                        $tempPath = $this->videoToAudioConverter->convert($tempPath);
                    }
                }

                return $this->processFromFile($libraryItem, $tempPath, $sourceUrl);
            } finally {
                foreach (array_unique([$sourceTempPath, $tempPath]) as $path) {
                    if (Storage::disk('public')->exists($path)) {
                        Storage::disk('public')->delete($path);
                    }
                }
            }

        } catch (\InvalidArgumentException $e) {
            return $this->handleProcessingError($libraryItem, $e);
        }
    }

    /**
     * Process media file from uploaded file path.
     */
    public function processFromFile(LibraryItem $libraryItem, string $filePath, ?string $sourceUrl = null): array
    {
        try {
            // Mark as processing if not already
            if ($libraryItem->processing_status !== ProcessingStatusType::PROCESSING) {
                $this->markAsProcessing($libraryItem);
            }

            // Verify file exists
            if (! $this->storageManager->fileExists($filePath)) {
                throw new \InvalidArgumentException('Temp file not found or inaccessible');
            }

            // Check for duplicates
            $duplicateResult = $this->duplicateProcessor->processFileDuplicate($libraryItem, $filePath);
            if ($duplicateResult['media_file']) {
                $existingMediaFile = $duplicateResult['media_file'];

                if ($this->storageManager->fileExists($existingMediaFile->file_path)) {
                    return $duplicateResult;
                }

                Log::warning('Media file record exists but file missing from disk, recreating', [
                    'library_item_id' => $libraryItem->id,
                    'media_file_id' => $existingMediaFile->id,
                    'file_path' => $existingMediaFile->file_path,
                ]);

                if (! MediaFileRetirementService::retire($existingMediaFile)) {
                    throw new \RuntimeException('Cannot replace a media file that is still in use.');
                }
            }

            // Validate and get file metadata
            $fullPath = Storage::disk('public')->path($filePath);
            $metadata = $this->validator->validate($fullPath);
            $fileHash = hash_file('sha256', $fullPath);

            DuplicateDetectionService::cleanupOrphanedByHash($fileHash);

            // Move file to permanent location
            $fileData = $this->storageManager->moveTempFile($filePath, $sourceUrl);
            $fileData = array_merge($fileData, $metadata);

            try {
                $mediaFile = MediaFile::create([
                    'user_id' => $libraryItem->user_id,
                    'file_path' => $fileData['file_path'],
                    'file_hash' => $fileData['file_hash'],
                    'mime_type' => $fileData['mime_type'],
                    'filesize' => $fileData['filesize'],
                    'duration' => $fileData['duration'] ?? null,
                    'source_url' => $sourceUrl,
                ]);
            } catch (QueryException $e) {
                $mediaFile = MediaFile::findByHash($fileData['file_hash']);

                if (! $mediaFile) {
                    throw $e;
                }

                if ($mediaFile->file_path !== $fileData['file_path']) {
                    Storage::disk('public')->delete($fileData['file_path']);
                }

                $libraryItem->media_file_id = $mediaFile->id;
                $libraryItem->update([
                    'processing_status' => ProcessingStatusType::COMPLETED,
                    'processing_completed_at' => now(),
                    'temp_file_path' => null,
                ]);

                return [
                    'is_duplicate' => false,
                    'media_file' => $mediaFile,
                    'message' => 'File already exists in system. Linked to existing media file.',
                ];
            }

            // Link to library item
            $libraryItem->media_file_id = $mediaFile->id;
            $libraryItem->update([
                'processing_status' => ProcessingStatusType::COMPLETED,
                'processing_completed_at' => now(),
                'temp_file_path' => null,
            ]);

            // Honor the add-time opt-in for automatic chapter generation.
            if ($libraryItem->fresh()?->auto_generate_chapters && $mediaFile->duration) {
                TranscribeMediaFile::withChain([new SegmentTranscriptIntoChapters($mediaFile)])
                    ->onConnection('chapters')
                    ->onQueue('chapters')
                    ->dispatch($mediaFile);
            }

            return [
                'is_duplicate' => false,
                'media_file' => $mediaFile,
                'message' => 'Media file processed successfully.',
            ];

        } catch (\InvalidArgumentException $e) {
            return $this->handleProcessingError($libraryItem, $e);
        }
    }

    /**
     * Mark library item as processing.
     */
    private function markAsProcessing(LibraryItem $libraryItem): void
    {
        $libraryItem->update([
            'processing_status' => ProcessingStatusType::PROCESSING,
            'processing_started_at' => now(),
            'processing_error' => null,
        ]);
    }

    /**
     * Handle processing errors.
     */
    private function handleProcessingError(LibraryItem $libraryItem, \Exception $e): array
    {
        $libraryItem->update([
            'processing_status' => ProcessingStatusType::FAILED,
            'processing_completed_at' => now(),
            'processing_error' => 'Media processing failed.',
        ]);

        return [
            'is_duplicate' => false,
            'media_file' => null,
            'error' => 'media_processing_failed',
            'message' => 'Media processing failed.',
        ];
    }
}
