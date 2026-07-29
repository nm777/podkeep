<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\StoreFeedRequest;
use App\Http\Requests\Api\V1\UpdateFeedRequest;
use App\Http\Resources\FeedResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

class FeedController extends Controller
{
    /**
     * Display a listing of the user's feeds.
     */
    public function index(): JsonResponse
    {
        $feeds = Auth::user()->feeds()
            ->withCount('items')
            ->latest()
            ->get();

        return FeedResource::collection($feeds)
            ->response()
            ->setStatusCode(200);
    }

    /**
     * Store a newly created feed.
     */
    public function store(StoreFeedRequest $request): JsonResponse
    {
        $validated = $request->validated();

        $feed = Auth::user()->feeds()->create([
            'title' => $validated['title'],
            'description' => $validated['description'] ?? null,
            'website_url' => $validated['website_url'] ?? null,
            'is_public' => $validated['is_public'] ?? false,
            'feed_type' => $validated['feed_type'] ?? 'append',
            'slug' => $this->generateUniqueSlug($validated['title']),
            'user_guid' => Str::uuid(),
            'token' => Str::random(64),
        ]);

        return (new FeedResource($feed))
            ->response()
            ->setStatusCode(201);
    }

    /**
     * Display the specified feed.
     */
    public function show(int $id): JsonResponse
    {
        $feed = Auth::user()->feeds()->findOrFail($id);

        return (new FeedResource($feed))
            ->response()
            ->setStatusCode(200);
    }

    /**
     * Update the specified feed.
     */
    public function update(UpdateFeedRequest $request, int $id): JsonResponse
    {
        $feed = Auth::user()->feeds()->findOrFail($id);

        $validated = $request->validated();

        $feed->update($validated);

        Cache::forget("rss.{$feed->id}");

        return (new FeedResource($feed))
            ->response()
            ->setStatusCode(200);
    }

    /**
     * Remove the specified feed.
     */
    public function destroy(int $id): JsonResponse
    {
        $feed = Auth::user()->feeds()->findOrFail($id);

        $feed->delete();

        return response()->json(null, 204);
    }

    private function generateUniqueSlug(string $title, ?int $excludeFeedId = null): string
    {
        $slug = Str::slug($title);
        $originalSlug = $slug;
        $count = 1;

        $query = Auth::user()->feeds()->where('slug', $slug);
        if ($excludeFeedId) {
            $query->where('id', '!=', $excludeFeedId);
        }

        while ($query->exists()) {
            $slug = $originalSlug.'-'.$count;
            $count++;

            $query = Auth::user()->feeds()->where('slug', $slug);
            if ($excludeFeedId) {
                $query->where('id', '!=', $excludeFeedId);
            }
        }

        return $slug;
    }
}
