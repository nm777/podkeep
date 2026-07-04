<?php

namespace App\Models;

use App\Enums\EpisodeOrderType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Feed extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'title',
        'description',
        'website_url',
        'cover_image_url',
        'is_public',
        'episode_order',
        'slug',
        'user_guid',
        'token',
    ];

    protected function casts(): array
    {
        return [
            'episode_order' => EpisodeOrderType::class,
        ];
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function items()
    {
        return $this->hasMany(FeedItem::class)->orderBy('sequence');
    }
}
