<?php

namespace App\Models;

use App\Services\DuplicateDetectionService;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class MediaFile extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'file_path',
        'file_hash',
        'mime_type',
        'filesize',
        'duration',
        'source_url',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function libraryItems()
    {
        return $this->hasMany(LibraryItem::class);
    }

    public function getPublicUrlAttribute(): string
    {
        return Storage::disk('public')->url($this->file_path);
    }

    public function getRssUrlAttribute(): string
    {
        return url('/files/'.$this->file_path);
    }

    /**
     * Find a media file by source URL.
     */
    public static function findBySourceUrl(string $sourceUrl): ?static
    {
        $query = static::where('source_url', $sourceUrl);

        return $query->first();
    }

    /**
     * Find a media file by file hash.
     */
    public static function findByHash(string $fileHash): ?static
    {
        return static::where('file_hash', $fileHash)->first();
    }

    public static function findDuplicateByFile(string $filePath): ?static
    {
        return DuplicateDetectionService::findGlobalDuplicate($filePath);
    }

    public static function findDuplicateByFileForUser(string $filePath, int $userId): ?static
    {
        return DuplicateDetectionService::findUserDuplicate($filePath, $userId);
    }

    /**
     * @deprecated Use findDuplicateByFile() instead
     */
    public static function isDuplicate(string $filePath): ?static
    {
        return static::findDuplicateByFile($filePath);
    }

    /**
     * @deprecated Use findDuplicateByFileForUser() instead
     */
    public static function isDuplicateForUser(string $filePath, int $userId): ?static
    {
        return static::findDuplicateByFileForUser($filePath, $userId);
    }
}
