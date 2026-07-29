<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\AttachFeedItemRequest;
use App\Http\Resources\FeedItemResource;
use App\Models\LibraryItem;
use App\Services\FeedItemOrderingService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;

class FeedItemController extends Controller
{
    /**
     * Display the feed's items ordered by sequence.
     */
    public function index(int $feedId): JsonResponse
    {
        $feed = Auth::user()->feeds()->findOrFail($feedId);

        $items = $feed->items()
            ->with('libraryItem')
            ->orderBy('sequence')
            ->get();

        return FeedItemResource::collection($items)
            ->response()
            ->setStatusCode(200);
    }

    /**
     * Attach a library item to the feed.
     */
    public function store(AttachFeedItemRequest $request, int $feedId, FeedItemOrderingService $feedItemOrdering): JsonResponse
    {
        $feed = Auth::user()->feeds()->findOrFail($feedId);

        $libraryItemId = (int) $request->validated('library_item_id');
        $sequence = $request->validated('sequence');

        if (! LibraryItem::where('id', $libraryItemId)->where('user_id', Auth::id())->exists()) {
            return response()->json([
                'message' => 'The selected library item does not belong to you.',
            ], 403);
        }

        $feedItem = $feedItemOrdering->append($feed, $libraryItemId, $sequence);

        abort_if($feedItem === null, 422, 'The library item is already attached to this feed.');

        $feedItem->load('libraryItem');

        Cache::forget("rss.{$feedId}");

        return (new FeedItemResource($feedItem))
            ->response()
            ->setStatusCode(201);
    }

    /**
     * Reorder the feed's items.
     */
    public function reorder(Request $request, int $feedId, FeedItemOrderingService $feedItemOrdering): JsonResponse
    {
        $feed = Auth::user()->feeds()->findOrFail($feedId);

        $validated = $request->validate([
            'items' => ['required', 'array'],
            'items.*.id' => ['required', 'integer', 'distinct'],
            'items.*.sequence' => ['required', 'integer', 'min:0', 'distinct'],
        ]);

        $items = $feedItemOrdering->reorder($feed, $validated['items']);

        Cache::forget("rss.{$feedId}");

        return FeedItemResource::collection($items)
            ->response()
            ->setStatusCode(200);
    }

    /**
     * Remove the specified item from the feed.
     */
    public function destroy(int $feedId, int $itemId): Response
    {
        $feed = Auth::user()->feeds()->findOrFail($feedId);

        $feed->items()->findOrFail($itemId)->delete();

        Cache::forget("rss.{$feedId}");

        return response()->noContent();
    }
}
