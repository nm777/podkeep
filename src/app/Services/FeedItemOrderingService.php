<?php

namespace App\Services;

use App\Models\Feed;
use App\Models\FeedItem;
use App\Models\LibraryItem;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class FeedItemOrderingService
{
    public function append(Feed $feed, int $libraryItemId, ?int $sequence = null): ?FeedItem
    {
        return DB::transaction(function () use ($feed, $libraryItemId, $sequence) {
            $feed = Feed::lockForUpdate()->findOrFail($feed->id);

            if ($feed->items()->where('library_item_id', $libraryItemId)->exists()) {
                return null;
            }

            $nextSequence = ($feed->items()->max('sequence') ?? -1) + 1;

            if ($sequence !== null && $sequence !== $nextSequence) {
                throw ValidationException::withMessages([
                    'sequence' => 'The sequence must be the next available position.',
                ]);
            }

            return $feed->items()->create([
                'library_item_id' => $libraryItemId,
                'sequence' => $nextSequence,
            ]);
        });
    }

    public function remove(Feed $feed, int $feedItemId): void
    {
        DB::transaction(function () use ($feed, $feedItemId): void {
            $feed = Feed::lockForUpdate()->findOrFail($feed->id);

            $feed->items()->findOrFail($feedItemId)->delete();

            $feedItems = $feed->items()->orderBy('sequence')->orderBy('id')->get();

            foreach ($feedItems as $feedItem) {
                $feedItem->update(['sequence' => -$feedItem->id]);
            }

            foreach ($feedItems as $sequence => $feedItem) {
                $feedItem->update(['sequence' => $sequence]);
            }
        });
    }

    /**
     * @param  array<int, array{id: int, sequence: int}>  $items
     * @return Collection<int, FeedItem>
     */
    public function reorder(Feed $feed, array $items): Collection
    {
        return DB::transaction(function () use ($feed, $items) {
            $feed = Feed::lockForUpdate()->findOrFail($feed->id);
            $submittedIds = array_column($items, 'id');
            $sequences = array_column($items, 'sequence');
            $feedItemIds = $feed->items()->pluck('id')->all();

            sort($submittedIds);
            sort($feedItemIds);
            sort($sequences);

            $expectedSequences = $items === [] ? [] : range(0, count($items) - 1);

            if ($submittedIds !== $feedItemIds || $sequences !== $expectedSequences) {
                throw ValidationException::withMessages([
                    'items' => 'Items must contain every feed item exactly once with sequences from zero to the item count minus one.',
                ]);
            }

            foreach ($feedItemIds as $id) {
                $feed->items()->whereKey($id)->update(['sequence' => -$id]);
            }

            foreach ($items as $item) {
                $feed->items()->whereKey($item['id'])->update(['sequence' => $item['sequence']]);
            }

            return $feed->items()->with('libraryItem')->orderBy('sequence')->get();
        });
    }

    /**
     * @param  array<int, array{library_item_id: int, sequence: int}>  $items
     */
    public function sync(Feed $feed, array $items): void
    {
        DB::transaction(function () use ($feed, $items): void {
            $feed = Feed::lockForUpdate()->findOrFail($feed->id);
            $libraryItemIds = array_column($items, 'library_item_id');
            $sequences = array_column($items, 'sequence');

            sort($sequences);

            if (count($libraryItemIds) !== count(array_unique($libraryItemIds))
                || $sequences !== ($items === [] ? [] : range(0, count($items) - 1))) {
                throw ValidationException::withMessages([
                    'items' => 'Items must have unique library items and contiguous sequences starting at zero.',
                ]);
            }

            if (LibraryItem::where('user_id', $feed->user_id)->whereIn('id', $libraryItemIds)->count() !== count($libraryItemIds)) {
                throw ValidationException::withMessages([
                    'items' => 'You can only add your own library items to feeds.',
                ]);
            }

            // Move current sequences out of the target range before assigning new ones.
            $feed->items()->update(['sequence' => DB::raw('-id')]);

            if ($libraryItemIds === []) {
                $feed->items()->delete();

                return;
            }

            $feed->items()->whereNotIn('library_item_id', $libraryItemIds)->delete();

            FeedItem::upsert(
                array_map(fn (array $item) => ['feed_id' => $feed->id, ...$item], $items),
                ['feed_id', 'library_item_id'],
                ['sequence'],
            );
        });
    }
}
