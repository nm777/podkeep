<?php

use App\Models\FeedItem;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Schema;

it('indexes public feed lookups by user guid and slug', function () {
    $index = collect(Schema::getIndexes('feeds'))->firstWhere('name', 'feeds_user_guid_slug_index');

    expect($index)
        ->not->toBeNull()
        ->and($index['columns'])->toBe(['user_guid', 'slug'])
        ->and($index['unique'])->toBeFalse();
});

it('enforces one feed item per feed and library item pair', function () {
    $feedItem = FeedItem::factory()->create();

    expect(fn () => FeedItem::factory()->create([
        'feed_id' => $feedItem->feed_id,
        'library_item_id' => $feedItem->library_item_id,
    ]))->toThrow(QueryException::class);
});
