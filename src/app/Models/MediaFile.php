<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property array<int, array<string, mixed>>|null $transcript
 * @property array<int, array<string, mixed>>|null $chapter_proposal
 */
class MediaFile extends Model
{
    use HasFactory;

    protected $hidden = [
        'file_hash',
        'source_url',
        'transcript',
        'chapter_proposal',
        'chapter_generation_error',
    ];

    protected $fillable = [
        'user_id',
        'is_public',
        'file_path',
        'file_hash',
        'mime_type',
        'filesize',
        'duration',
        'source_url',
        'transcript',
        'chapter_generation_status',
        'chapter_generation_version',
        'chapter_proposal',
        'chapter_proposal_for_hash',
        'chapter_generation_error',
    ];

    protected $attributes = [
        'is_public' => false,
    ];

    protected function casts(): array
    {
        return [
            'is_public' => 'boolean',
            'transcript' => 'array',
            'chapter_proposal' => 'array',
        ];
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * @return HasMany<LibraryItem, $this>
     */
    public function libraryItems(): HasMany
    {
        return $this->hasMany(LibraryItem::class);
    }

    public function chapters(): HasMany
    {
        return $this->hasMany(Chapter::class)->orderBy('start_time');
    }

    public function getRssUrlAttribute(): string
    {
        return route('files.show', ['file_path' => $this->file_path]);
    }

    public static function findBySourceUrl(string $sourceUrl): ?static
    {
        return static::where('source_url', $sourceUrl)->first();
    }

    public static function findByHash(string $fileHash): ?static
    {
        return static::where('file_hash', $fileHash)->first();
    }

    public static function syncPublicStatusForUser(int $userId): void
    {
        static::where('user_id', $userId)->update(['is_public' => false]);

        static::where('user_id', $userId)
            ->whereHas('libraryItems', function ($query) use ($userId) {
                $query->where('user_id', $userId)
                    ->whereHas('feedItems.feed', function ($query) use ($userId) {
                        $query->where('user_id', $userId)->where('is_public', true);
                    });
            })
            ->update(['is_public' => true]);
    }
}
