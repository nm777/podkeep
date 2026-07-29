<?php

namespace App\Services\MediaProcessing;

use App\Models\LibraryItem;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class MediaRedownloader
{
    public function __construct(
        private MediaDownloader $downloader,
        private MediaStorageManager $storageManager,
        private MediaValidator $validator,
    ) {}

    public function redownload(LibraryItem $libraryItem): array
    {
        $mediaFile = $libraryItem->mediaFile;

        if (! $mediaFile) {
            throw new \Exception('No media file associated with this library item');
        }

        if (! $mediaFile->source_url) {
            throw new \Exception('Cannot redownload: no source URL available for this media file');
        }

        if ($mediaFile->user_id !== $libraryItem->user_id) {
            throw new \Exception('Cannot redownload a media file owned by another user');
        }

        $fileExisted = $this->storageManager->fileExists($mediaFile->file_path);

        try {
            $tempPath = $this->downloader->downloadFromUrl($mediaFile->source_url);

            $storageInfo = $this->storageManager->moveTempFile($tempPath, $mediaFile->source_url);

            $oldFilePath = $mediaFile->file_path;
            $oldHash = $mediaFile->file_hash;

            $hashChanged = $storageInfo['file_hash'] !== $oldHash;

            $fullPath = Storage::disk('public')->path($storageInfo['file_path']);
            $metadata = $this->validator->validate($fullPath);

            $mediaFile->update([
                'file_path' => $storageInfo['file_path'],
                'file_hash' => $storageInfo['file_hash'],
                'filesize' => $storageInfo['filesize'],
                'mime_type' => $metadata['mime_type'],
                'duration' => $metadata['duration'] ?? null,
            ]);

            if ($hashChanged && $fileExisted && $oldFilePath !== $storageInfo['file_path']) {
                Storage::disk('public')->delete($oldFilePath);
            }

            return [
                'success' => true,
                'file_existed' => $fileExisted,
                'hash_changed' => $hashChanged,
                'old_hash' => $oldHash,
                'new_hash' => $storageInfo['file_hash'],
            ];
        } catch (\Exception $e) {
            Log::error('Media redownload failed', [
                'library_item_id' => $libraryItem->id,
                'media_file_id' => $mediaFile->id,
                'source_url' => $mediaFile->source_url,
                'error' => $e->getMessage(),
            ]);

            throw $e;
        }
    }
}
