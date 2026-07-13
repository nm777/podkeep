<?php

namespace App\Enums;

enum MediaType: string
{
    case Audio = 'audio';
    case Video = 'video';

    public function getDisplayName(): string
    {
        return match ($this) {
            self::Audio => 'Audio',
            self::Video => 'Video',
        };
    }

    public function isAudio(): bool
    {
        return $this === self::Audio;
    }

    public function isVideo(): bool
    {
        return $this === self::Video;
    }
}
