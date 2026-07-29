<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Storage;

/**
 * @property array<int, array<string, mixed>>|null $transcript
 * @property array<int, array<string, mixed>>|null $chapter_proposal
 */
class MediaFile extends Model
{
    use HasFactory;

    protected $hidden = [
        'source_url',
    ];

    protected $fillable = [
        'user_id',
        'file_path',
        'file_hash',
        'mime_type',
        'filesize',
        'duration',
        'source_url',
        'transcript',
        'chapter_generation_status',
        'chapter_proposal',
        'chapter_proposal_for_hash',
        'chapter_generation_error',
    ];

    protected function casts(): array
    {
        return [
            'transcript' => 'array',
            'chapter_proposal' => 'array',
        ];
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function libraryItems()
    {
        return $this->hasMany(LibraryItem::class);
    }

    public function chapters(): HasMany
    {
        return $this->hasMany(Chapter::class)->orderBy('start_time');
    }

    public function getPublicUrlAttribute(): string
    {
        return Storage::disk('public')->url($this->file_path);
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
}
