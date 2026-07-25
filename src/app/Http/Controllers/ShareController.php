<?php

namespace App\Http\Controllers;

use App\Models\Feed;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ShareController extends Controller
{
    public function show(Request $request, string $user_guid, string $feed_slug)
    {
        $feed = Feed::where('user_guid', $user_guid)
            ->where('slug', $feed_slug)
            ->with(['items.libraryItem.mediaFile.chapters'])
            ->first();

        if (! $feed) {
            abort(404);
        }

        if (! $feed->is_public && $request->token !== $feed->token && $request->user()?->id !== $feed->user_id) {
            abort(404);
        }

        $token = $feed->is_public ? null : $feed->token;

        $items = $feed->feed_type->isStatic()
            ? $feed->items->sortBy('sequence')
            : $feed->items->sortByDesc('created_at');

        $episodes = $items
            ->filter(fn ($item) => $item->libraryItem
                && $item->libraryItem->processing_status?->value === 'completed'
                && $item->libraryItem->mediaFile)
            ->map(fn ($item) => [
                'id' => $item->libraryItem->id,
                'title' => $item->libraryItem->title,
                'description' => $item->libraryItem->description,
                'published_at' => $item->libraryItem->published_at?->format('Y-m-d'),
                'duration' => $item->libraryItem->mediaFile->duration,
                'media_url' => $this->buildMediaUrl($item->libraryItem->mediaFile->file_path, $token),
                'chapters' => $item->libraryItem->mediaFile->chapters->map(fn ($chapter) => [
                    'start_time' => $chapter->start_time,
                    'title' => $chapter->title,
                ]),
            ])
            ->values()
            ->all();

        $rssUrl = url("/rss/{$feed->user_guid}/{$feed->slug}");
        if (! $feed->is_public) {
            $rssUrl .= '?token='.$token;
        }

        return Inertia::render('share/show', [
            'feed' => [
                'title' => $feed->title,
                'description' => $feed->description,
                'cover_image_url' => $feed->cover_image_url,
            ],
            'episodes' => $episodes,
            'rssUrl' => $rssUrl,
            'isPublic' => (bool) $feed->is_public,
        ]);
    }

    private function buildMediaUrl(string $filePath, ?string $token): string
    {
        $url = '/files/'.$filePath;

        if ($token) {
            $url .= '?feed_token='.$token;
        }

        return $url;
    }
}
