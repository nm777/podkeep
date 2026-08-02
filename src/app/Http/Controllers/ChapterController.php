<?php

namespace App\Http\Controllers;

use App\Http\Requests\ChapterSyncRequest;
use App\Jobs\SegmentTranscriptIntoChapters;
use App\Jobs\TranscribeMediaFile;
use App\Models\LibraryItem;
use App\Models\MediaFile;
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
        abort_unless(! $mediaFile || $mediaFile->user_id === $library_item->user_id, 403);

        if (! $mediaFile || ! $mediaFile->duration) {
            return back()->with('warning', 'Chapters require a processed media file with a known duration.');
        }

        /** @var array<int, array{start_time: int, title: string}> $chapters */
        $chapters = $request->validated()['chapters'] ?? [];

        DB::transaction(function () use ($mediaFile, $chapters) {
            $mediaFile = MediaFile::query()->lockForUpdate()->findOrFail($mediaFile->id);
            $mediaFile->chapters()->delete();
            foreach ($chapters as $chapter) {
                $mediaFile->chapters()->create([
                    'start_time' => $chapter['start_time'],
                    'title' => $chapter['title'],
                ]);
            }

            $mediaFile->update([
                'chapter_generation_version' => $mediaFile->chapter_generation_version + 1,
                'chapter_generation_status' => null,
                'chapter_generation_error' => null,
            ]);
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
        abort_unless(! $mediaFile || $mediaFile->user_id === $library_item->user_id, 403);

        if (! $mediaFile || ! $mediaFile->duration) {
            return back()->with('warning', 'Chapters require a processed media file with a known duration.');
        }

        $mediaFile = DB::transaction(function () use ($mediaFile) {
            $mediaFile = MediaFile::query()->lockForUpdate()->findOrFail($mediaFile->id);

            if (in_array($mediaFile->chapter_generation_status, ['pending', 'processing'], true)) {
                return null;
            }

            // "Regenerate" forces fresh segmentation instead of skipping the cached proposal.
            $updates = [
                'chapter_generation_version' => $mediaFile->chapter_generation_version + 1,
                'chapter_generation_status' => 'pending',
                'chapter_generation_error' => null,
                'chapter_proposal' => null,
            ];
            if ($mediaFile->chapter_generation_status === 'completed') {
                $updates['chapter_proposal_for_hash'] = null;
            }
            $mediaFile->update($updates);

            return $mediaFile;
        });

        if (! $mediaFile) {
            return back()->with('warning', 'Chapter generation is already in progress.');
        }

        TranscribeMediaFile::withChain([new SegmentTranscriptIntoChapters($mediaFile, $mediaFile->chapter_generation_version)])
            ->onConnection('chapters')
            ->onQueue('chapters')
            ->dispatch($mediaFile, $mediaFile->chapter_generation_version);

        return back()->with('success', 'Generating chapters in the background — you can leave the page; it keeps running even if you navigate away.');
    }
}
