<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Chapter extends Model
{
    use HasFactory;

    protected $fillable = [
        'media_file_id',
        'start_time',
        'title',
    ];

    /**
     * @return BelongsTo<MediaFile, $this>
     */
    public function mediaFile(): BelongsTo
    {
        return $this->belongsTo(MediaFile::class);
    }

    /**
     * Format start_time (seconds) as the Podlove H:MM:SS chapter start.
     */
    public function formattedStart(): string
    {
        $h = intdiv($this->start_time, 3600);
        $m = intdiv($this->start_time % 3600, 60);
        $s = $this->start_time % 60;

        return sprintf('%d:%02d:%02d', $h, $m, $s);
    }
}
