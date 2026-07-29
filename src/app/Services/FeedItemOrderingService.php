<?php

namespace App\Services;

use App\Models\Feed;
use App\Models\FeedItem;
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
}
