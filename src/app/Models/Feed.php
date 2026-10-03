<?php

namespace App\Models;

use App\Enums\FeedType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Feed extends Model
{
    use HasFactory;

    protected $attributes = [
        'feed_type' => 'append',
    ];

    protected $fillable = [
        'user_id',
        'title',
        'description',
        'website_url',
        'cover_image_url',
        'is_public',
        'is_hidden_from_selector',
        'feed_type',
        'slug',
        'user_guid',
    ];

    protected function casts(): array
    {
        return [
            'feed_type' => FeedType::class,
            'is_hidden_from_selector' => 'boolean',
        ];
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * @return HasMany<FeedItem, $this>
     */
    public function items(): HasMany
    {
        return $this->hasMany(FeedItem::class)->orderBy('sequence');
    }
}
