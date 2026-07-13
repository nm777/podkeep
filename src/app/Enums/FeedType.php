<?php

namespace App\Enums;

enum FeedType: string
{
    case Static = 'static';
    case Append = 'append';

    public function getDisplayName(): string
    {
        return match ($this) {
            self::Static => 'Static (Chapters)',
            self::Append => 'Append (Ongoing)',
        };
    }

    public function isStatic(): bool
    {
        return $this === self::Static;
    }

    public function isAppend(): bool
    {
        return $this === self::Append;
    }
}
