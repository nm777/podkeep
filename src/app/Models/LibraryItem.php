<?php

namespace App\Models;

use App\Enums\MediaType;
use App\Enums\ProcessingStatusType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LibraryItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'media_file_id',
        'title',
        'description',
        'source_type',
        'media_type',
        'auto_generate_chapters',
        'source_url',
        'published_at',
        'display_date',
        'is_duplicate',
        'duplicate_detected_at',
        'processing_status',
        'processing_started_at',
        'processing_completed_at',
        'processing_error',
        'temp_file_path',
    ];

    protected $attributes = [
        'media_type' => 'audio',
    ];

    protected function casts(): array
    {
        return [
            'is_duplicate' => 'boolean',
            'duplicate_detected_at' => 'datetime',
            'published_at' => 'date',
            'display_date' => 'date',
            'processing_started_at' => 'datetime',
            'processing_completed_at' => 'datetime',
            'processing_status' => ProcessingStatusType::class,
            'media_type' => MediaType::class,
            'auto_generate_chapters' => 'boolean',
        ];
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * @return BelongsTo<MediaFile, $this>
     */
    public function mediaFile(): BelongsTo
    {
        return $this->belongsTo(MediaFile::class);
    }

    /**
     * Find a library item by source URL and user id
     */
    public static function findBySourceUrlForUser(string $sourceUrl, int $userId): ?static
    {
        $query = static::where('source_url', $sourceUrl)->where('user_id', $userId);

        return $query->first();
    }

    /**
     * Find a library item by media file hash for a specific user.
     */
    public static function findByHashForUser(string $fileHash, int $userId): ?static
    {
        return static::whereHas('mediaFile', function ($query) use ($fileHash) {
            $query->where('file_hash', $fileHash);
        })->where('user_id', $userId)->first();
    }

    public function isProcessing(): bool
    {
        return $this->processing_status?->isProcessing() ?? false;
    }

    public function isPending(): bool
    {
        return $this->processing_status?->isPending() ?? false;
    }

    public function hasCompleted(): bool
    {
        return $this->processing_status?->hasCompleted() ?? false;
    }

    public function hasFailed(): bool
    {
        return $this->processing_status?->hasFailed() ?? false;
    }

    public function getProcessingStatusTextAttribute(): string
    {
        return $this->processing_status?->getDisplayName() ?? 'Unknown';
    }

    public function feedItems()
    {
        return $this->hasMany(FeedItem::class);
    }

    public function feeds()
    {
        return $this->belongsToMany(Feed::class, 'feed_items')
            ->withPivot('sequence')
            ->withTimestamps();
    }
}
