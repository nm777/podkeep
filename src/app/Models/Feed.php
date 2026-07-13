<?php

namespace App\Models;

use App\Enums\FeedType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

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
        'feed_type',
        'slug',
        'user_guid',
        'token',
    ];

    protected function casts(): array
    {
        return [
            'feed_type' => \App\Enums\FeedType::class,
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
