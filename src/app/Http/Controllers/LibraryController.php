<?php

namespace App\Http\Controllers;

use App\Enums\ProcessingStatusType;
use App\Http\Requests\LibraryItemRequest;
use App\Http\Requests\UpdateLibraryItemRequest;
use App\Jobs\ProcessYouTubeAudio;
use App\Jobs\RedownloadMediaFile;
use App\Models\Feed;
use App\Models\FeedItem;
use App\Models\LibraryItem;
use App\Services\SourceProcessors\SourceProcessorFactory;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class LibraryController extends Controller
{
    public function store(LibraryItemRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        [$sourceType, $sourceUrl] = $this->getSourceTypeAndUrl($request);

        if ($redirectResponse = SourceProcessorFactory::validate($sourceType, $sourceUrl)) {
            return $redirectResponse;
        }

        // Use strategy pattern to process different source types
        $processor = SourceProcessorFactory::create($sourceType);
        [$libraryItem, $message] = $processor->process($request, $validated, $sourceType, $sourceUrl);

        // Add feed information to success message if feeds were selected
        if (! empty($validated['feed_ids'])) {
            $feedCount = count($validated['feed_ids']);
            $message .= " Item will be added to {$feedCount} feed".($feedCount > 1 ? 's' : '').' once processing completes.';
        }

        return redirect()->route('library.index')
            ->with('success', $message);
    }

    public function destroy($id): RedirectResponse
    {
        $libraryItem = LibraryItem::findOrFail($id);

        Gate::authorize('delete', $libraryItem);

        $mediaFile = $libraryItem->mediaFile;

        $feedIds = $libraryItem->feedItems()->pluck('feed_id');
        foreach ($feedIds as $feedId) {
            Cache::forget("rss.{$feedId}");
        }

        $libraryItem->delete();

        if ($mediaFile && $mediaFile->libraryItems()->count() === 0) {
            Storage::disk('public')->delete($mediaFile->file_path);
            $mediaFile->delete();
        }

        return redirect()->route('library.index')
            ->with('success', 'Media file removed from your library.');
    }

    public function retry($id): RedirectResponse
    {
        $libraryItem = LibraryItem::findOrFail($id);

        Gate::authorize('retry', $libraryItem);

        if (! $libraryItem->hasFailed()) {
            return redirect()->route('library.index')
                ->with('warning', 'Only failed items can be retried.');
        }

        $libraryItem->update([
            'processing_status' => ProcessingStatusType::PENDING,
            'processing_error' => null,
            'processing_started_at' => now(),
            'processing_completed_at' => null,
        ]);

        $sourceType = $libraryItem->source_type;
        $processor = SourceProcessorFactory::create($sourceType);
        $processor->retry($libraryItem);

        return redirect()->route('library.index')
            ->with('success', 'Processing has been restarted.');
    }

    public function redownload($id): RedirectResponse
    {
        $libraryItem = LibraryItem::findOrFail($id);

        Gate::authorize('update', $libraryItem);

        if (! $libraryItem->mediaFile) {
            return back()->with('error', 'No media file associated with this library item.');
        }

        if (! $libraryItem->mediaFile->source_url) {
            return back()->with('error', 'Cannot redownload: no source URL available for this media file.');
        }

        if ($libraryItem->mediaFile->user_id !== $libraryItem->user_id) {
            return back()->with('error', 'Cannot redownload a media file owned by another user.');
        }

        $libraryItem->update([
            'processing_status' => ProcessingStatusType::PROCESSING,
            'processing_started_at' => now(),
            'processing_completed_at' => null,
            'processing_error' => null,
        ]);

        if ($libraryItem->source_type === 'youtube') {
            dispatch(new ProcessYouTubeAudio($libraryItem, $libraryItem->source_url));
        } else {
            dispatch(new RedownloadMediaFile($libraryItem));
        }

        return back()->with('success', 'Media file is being redownloaded.');
    }

    public function update(UpdateLibraryItemRequest $request, $id): RedirectResponse
    {
        $libraryItem = LibraryItem::findOrFail($id);

        Gate::authorize('update', $libraryItem);

        $validated = $request->validated();

        $libraryItem->update($validated);

        foreach ($libraryItem->feedItems()->pluck('feed_id') as $feedId) {
            Cache::forget("rss.{$feedId}");
        }

        return back()->with('success', 'Media file details updated successfully.');
    }

    public function attachFeed(Request $request, int $id): RedirectResponse
    {
        $libraryItem = LibraryItem::findOrFail($id);
        Gate::authorize('update', $libraryItem);

        $validated = $request->validate([
            'feed_id' => ['required', 'integer', Rule::exists('feeds', 'id')->where('user_id', $libraryItem->user_id)],
        ]);

        $feed = Feed::find($validated['feed_id']);

        $alreadyInFeed = FeedItem::where('feed_id', $feed->id)
            ->where('library_item_id', $libraryItem->id)
            ->exists();

        if (! $alreadyInFeed) {
            $maxSequence = $feed->items()->max('sequence') ?? 0;

            FeedItem::create([
                'feed_id' => $feed->id,
                'library_item_id' => $libraryItem->id,
                'sequence' => $maxSequence + 1,
            ]);

            Cache::forget("rss.{$feed->id}");
        }

        return back()->with('success', "Added to {$feed->title}.");
    }

    private function getSourceTypeAndUrl(LibraryItemRequest $request): array
    {
        $sourceType = $request->input('source_type', $request->hasFile('file') ? 'upload' : 'url');
        $sourceUrl = $request->input('source_url', $request->input('url'));

        return [$sourceType, $sourceUrl];
    }
}
