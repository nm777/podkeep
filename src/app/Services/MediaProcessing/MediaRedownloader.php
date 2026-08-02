<?php

namespace App\Services\MediaProcessing;

use App\Models\LibraryItem;
use App\Models\MediaFile;
use App\Services\MediaFileRetirementService;
use Illuminate\Support\Facades\DB;
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
        $tempPath = null;
        $storageInfo = null;
        $finalPathExisted = false;

        try {
            $tempPath = $this->downloader->downloadFromUrl($mediaFile->source_url);

            $fullPath = Storage::disk('media')->path($tempPath);
            $metadata = $this->validator->validate($fullPath);
            $finalPath = 'media/'.hash_file('sha256', $fullPath).'.'.pathinfo($fullPath, PATHINFO_EXTENSION);
            $finalPathExisted = Storage::disk('media')->exists($finalPath);

            $storageInfo = $this->storageManager->moveTempFile($tempPath, $mediaFile->source_url);

            $oldFilePath = $mediaFile->file_path;
            $oldHash = $mediaFile->file_hash;

            $hashChanged = $storageInfo['file_hash'] !== $oldHash;
            $hasOtherLibraryItems = false;

            $replacement = DB::transaction(function () use ($libraryItem, $mediaFile, $storageInfo, $metadata, $hashChanged, &$hasOtherLibraryItems): ?MediaFile {
                $mediaFile = MediaFile::query()->lockForUpdate()->findOrFail($mediaFile->id);
                $hasOtherLibraryItems = $mediaFile->libraryItems()
                    ->whereKeyNot($libraryItem->id)
                    ->exists();
                $replacement = $hashChanged ? MediaFile::findByHash($storageInfo['file_hash']) : null;

                if ($replacement || ($hashChanged && $hasOtherLibraryItems)) {
                    $replacement ??= MediaFile::create([
                        'user_id' => $libraryItem->user_id,
                        'file_path' => $storageInfo['file_path'],
                        'file_hash' => $storageInfo['file_hash'],
                        'filesize' => $storageInfo['filesize'],
                        'mime_type' => $metadata['mime_type'],
                        'duration' => $metadata['duration'] ?? null,
                        'source_url' => $mediaFile->source_url,
                    ]);

                    $libraryItem->updateQuietly(['media_file_id' => $replacement->id]);

                    return $replacement;
                }

                $mediaFile->update([
                    'file_path' => $storageInfo['file_path'],
                    'file_hash' => $storageInfo['file_hash'],
                    'filesize' => $storageInfo['filesize'],
                    'mime_type' => $metadata['mime_type'],
                    'duration' => $metadata['duration'] ?? null,
                    ...($hashChanged ? [
                        'chapter_generation_version' => DB::raw('chapter_generation_version + 1'),
                        'transcript' => null,
                        'chapter_generation_status' => null,
                        'chapter_proposal' => null,
                        'chapter_proposal_for_hash' => null,
                        'chapter_generation_error' => null,
                    ] : []),
                ]);

                if ($hashChanged) {
                    $mediaFile->chapters()->delete();
                }

                return null;
            });

            if ($replacement && ! $hasOtherLibraryItems) {
                MediaFileRetirementService::retire($mediaFile);
            }

            if ($replacement && $replacement->file_path !== $storageInfo['file_path']) {
                if (Storage::disk('media')->exists($replacement->file_path)) {
                    Storage::disk('media')->delete($storageInfo['file_path']);
                } else {
                    Storage::disk('media')->move($storageInfo['file_path'], $replacement->file_path);
                }
            }

            if ($hashChanged && $fileExisted && $oldFilePath !== $storageInfo['file_path']
                && ! MediaFile::where('file_path', $oldFilePath)->exists()) {
                Storage::disk('media')->delete($oldFilePath);
            }

            ($replacement
                ? collect([$libraryItem])
                : $mediaFile->libraryItems()->get()
            )->each->forgetRssCache();

            return [
                'success' => true,
                'file_existed' => $fileExisted,
                'hash_changed' => $hashChanged,
                'old_hash' => $oldHash,
                'new_hash' => $storageInfo['file_hash'],
            ];
        } catch (\Exception $e) {
            if ($storageInfo && ! $finalPathExisted) {
                Storage::disk('media')->delete($storageInfo['file_path']);
            }

            if ($tempPath) {
                $this->storageManager->cleanupTempFile($tempPath);
            }

            Log::error('Media redownload failed', [
                'library_item_id' => $libraryItem->id,
                'media_file_id' => $mediaFile->id,
                'error_code' => 'media_redownload_failed',
                'message' => 'Media redownload failed.',
            ]);

            throw $e;
        }
    }
}
