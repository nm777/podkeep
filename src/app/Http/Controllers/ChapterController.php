<?php

namespace App\Http\Controllers;

use App\Http\Requests\ChapterSyncRequest;
use App\Jobs\SegmentTranscriptIntoChapters;
use App\Jobs\TranscribeMediaFile;
use App\Models\LibraryItem;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;

class ChapterController extends Controller
{
    public function sync(LibraryItem $library_item, ChapterSyncRequest $request): RedirectResponse
    {
        Gate::authorize('update', $library_item);

        $mediaFile = $library_item->mediaFile;
        if (! $mediaFile || ! $mediaFile->duration) {
            return back()->with('warning', 'Chapters require a processed media file with a known duration.');
        }

        /** @var array<int, array{start_time: int, title: string}> $chapters */
        $chapters = $request->validated()['chapters'] ?? [];

        DB::transaction(function () use ($mediaFile, $chapters) {
            $mediaFile->chapters()->delete();
            foreach ($chapters as $chapter) {
                $mediaFile->chapters()->create([
                    'start_time' => $chapter['start_time'],
                    'title' => $chapter['title'],
                ]);
            }
        });

        // Invalidate the RSS cache for every feed containing this media file.
        foreach ($mediaFile->libraryItems()->with('feedItems')->get() as $libraryItem) {
            foreach ($libraryItem->feedItems as $feedItem) {
                Cache::forget("rss.{$feedItem->feed_id}");
            }
        }

        return back()->with('success', 'Chapters updated.');
    }

    public function generate(LibraryItem $library_item): RedirectResponse
    {
        Gate::authorize('update', $library_item);

        $mediaFile = $library_item->mediaFile;
        if (! $mediaFile || ! $mediaFile->duration) {
            return back()->with('warning', 'Chapters require a processed media file with a known duration.');
        }

        $mediaFile->update(['chapter_generation_status' => 'pending', 'chapter_generation_error' => null]);

        TranscribeMediaFile::withChain([new SegmentTranscriptIntoChapters($mediaFile)])
            ->onQueue('chapters')
            ->dispatch($mediaFile);

        return back()->with('success', 'Generating chapters — this runs in the background.');
    }
}
