<?php

namespace App\Http\Controllers;

use App\Http\Requests\FeedRequest;
use App\Models\Feed;
use App\Models\LibraryItem;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Str;
use Inertia\Inertia;

class FeedController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $feeds = Auth::user()->feeds()->latest()->get();

        if (request()->expectsJson()) {
            return response()->json($feeds);
        }

        return redirect()->route('dashboard');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(FeedRequest $request)
    {
        $validated = $request->validated();

        $slug = $this->generateUniqueSlug($validated['title']);

        $feed = Auth::user()->feeds()->create([
            'title' => $validated['title'],
            'description' => $validated['description'] ?? null,
            'website_url' => $validated['website_url'] ?? null,
            'slug' => $slug,
            'user_guid' => Str::uuid(),
            'token' => Str::random(64),
            'is_public' => $validated['is_public'] ?? false,
            'is_hidden_from_selector' => $validated['is_hidden_from_selector'] ?? false,
            'feed_type' => $validated['feed_type'] ?? 'append',
        ]);

        return redirect()->route('feeds.edit', $feed)->with('success', 'Feed created successfully!');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Feed $feed)
    {
        Gate::authorize('update', $feed);

        $direction = $feed->feed_type->isStatic() ? 'asc' : 'desc';

        $feed->load([
            'items' => fn ($q) => $feed->feed_type->isStatic()
                ? $q->reorder()->orderBy('sequence', 'asc')
                : $q->reorder()->orderBy('created_at', 'desc'),
            'items.libraryItem',
            'items.libraryItem.mediaFile',
        ]);

        $userLibraryItems = Auth::user()->libraryItems()->with('mediaFile')->orderBy('created_at', 'desc')->get();

        return Inertia::render('feeds/edit', [
            'feed' => $feed,
            'userLibraryItems' => $userLibraryItems,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(FeedRequest $request, Feed $feed)
    {
        Gate::authorize('update', $feed);

        $validated = $request->validated();

        $wasStatic = $feed->feed_type->isStatic();

        $feed->update([
            'title' => $validated['title'],
            'description' => $validated['description'] ?? null,
            'website_url' => $validated['website_url'] ?? null,
            'is_public' => $validated['is_public'] ?? false,
            'is_hidden_from_selector' => $validated['is_hidden_from_selector'] ?? false,
            'feed_type' => $validated['feed_type'] ?? $feed->feed_type,
        ]);

        // When switching to Append, re-order items by addition recency
        if (($validated['feed_type'] ?? null) === 'append' && $wasStatic) {
            $items = $feed->items()->reorder()->orderBy('created_at', 'desc')->get();
            foreach ($items as $index => $item) {
                $item->update(['sequence' => $index]);
            }
        }

        if (isset($validated['items'])) {
            $this->syncFeedItems($feed, $validated['items']);
        }

        // Update display dates if provided (for Append feeds)
        if (isset($validated['display_dates'])) {
            foreach ($validated['display_dates'] as $libraryItemId => $date) {
                if ($date) {
                    LibraryItem::where('id', $libraryItemId)
                        ->where('user_id', $feed->user_id)
                        ->update(['display_date' => $date]);
                }
            }
        }

        // Clear RSS cache when feed is updated
        Cache::forget("rss.{$feed->id}");

        return redirect()->route('feeds.edit', $feed)->with('success', 'Feed updated successfully!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Feed $feed)
    {
        Gate::authorize('delete', $feed);

        // Clear RSS cache before deleting
        Cache::forget("rss.{$feed->id}");

        $feed->delete();

        if (request()->expectsJson()) {
            return response()->json(null, 204);
        }

        return redirect()->route('dashboard')->with('success', 'Feed deleted successfully!');
    }

    private function generateUniqueSlug(string $title): string
    {
        $slug = Str::slug($title);
        $originalSlug = $slug;
        $count = 1;

        $query = Auth::user()->feeds()->where('slug', $slug);

        while ($query->exists()) {
            $slug = $originalSlug.'-'.$count;
            $count++;

            $query = Auth::user()->feeds()->where('slug', $slug);
        }

        return $slug;
    }

    private function syncFeedItems(Feed $feed, array $items): void
    {
        $newItemIds = collect($items)->pluck('library_item_id');

        if ($newItemIds->isEmpty()) {
            $feed->items()->delete();

            return;
        }

        $feed->items()
            ->whereNotIn('library_item_id', $newItemIds)
            ->delete();

        foreach ($items as $item) {
            $feed->items()->updateOrCreate(
                [
                    'library_item_id' => $item['library_item_id'],
                ],
                [
                    'sequence' => $item['sequence'],
                ]
            );
        }
    }
}
