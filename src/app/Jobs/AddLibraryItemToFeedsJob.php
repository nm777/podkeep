<?php

namespace App\Jobs;

use App\Models\Feed;
use App\Models\LibraryItem;
use App\Services\FeedItemOrderingService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Cache;

class AddLibraryItemToFeedsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        public LibraryItem $libraryItem,
        public array $feedIds
    ) {}

    public function handle(FeedItemOrderingService $feedItemOrdering): void
    {
        $feeds = Feed::whereIn('id', $this->feedIds)
            ->where('user_id', $this->libraryItem->user_id)
            ->get();

        foreach ($feeds as $feed) {
            $feedItemOrdering->append($feed, $this->libraryItem->id);

            Cache::forget("rss.{$feed->id}");
        }
    }
}
