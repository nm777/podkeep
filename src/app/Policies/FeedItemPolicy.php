<?php

namespace App\Policies;

use App\Models\Feed;
use App\Models\User;

class FeedItemPolicy
{
    public function attach(User $user, Feed $feed): bool
    {
        return $user->id === $feed->user_id;
    }

    public function detach(User $user, Feed $feed): bool
    {
        return $user->id === $feed->user_id;
    }

    public function reorder(User $user, Feed $feed): bool
    {
        return $user->id === $feed->user_id;
    }
}
