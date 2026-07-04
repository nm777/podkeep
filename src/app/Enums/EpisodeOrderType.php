<?php

namespace App\Enums;

enum EpisodeOrderType: string
{
    case NewestFirst = 'newest_first';
    case Chronological = 'chronological';

    public function getDisplayName(): string
    {
        return match ($this) {
            self::NewestFirst => 'Newest First',
            self::Chronological => 'Chronological',
        };
    }

    public function isNewestFirst(): bool
    {
        return $this === self::NewestFirst;
    }

    public function isChronological(): bool
    {
        return $this === self::Chronological;
    }
}
