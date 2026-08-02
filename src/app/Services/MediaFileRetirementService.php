<?php

namespace App\Services;

use App\Models\MediaFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class MediaFileRetirementService
{
    public static function retire(MediaFile $mediaFile): bool
    {
        /** @var array{file_path: string, delete_storage: bool}|null $result */
        $result = DB::transaction(function () use ($mediaFile): ?array {
            $lockedMediaFile = MediaFile::lockForUpdate()->find($mediaFile->id);

            if (! $lockedMediaFile || $lockedMediaFile->libraryItems()->exists()) {
                return null;
            }

            $filePath = $lockedMediaFile->file_path;
            $lockedMediaFile->delete();

            return [
                'file_path' => $filePath,
                'delete_storage' => ! MediaFile::where('file_path', $filePath)->exists(),
            ];
        });

        if (! $result) {
            return false;
        }

        if ($result['delete_storage']) {
            Storage::disk('media')->delete($result['file_path']);
        }

        return true;
    }
}
