<?php

namespace App\Http\Controllers\Api\V1;

use App\Enums\ProcessingStatusType;
use App\Http\Controllers\Controller;
use App\Http\Resources\LibraryItemResource;
use App\Jobs\ProcessYouTubeAudio;
use App\Jobs\RedownloadMediaFile;
use App\Services\SourceProcessors\SourceProcessorFactory;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

class MediaProcessingController extends Controller
{
    /**
     * Retry processing for a failed library item.
     */
    public function retry(int $id): JsonResponse
    {
        $item = Auth::user()->libraryItems()->findOrFail($id);

        if (! $item->hasFailed()) {
            return response()->json(['message' => 'Only failed items can be retried.'], 422);
        }

        $item->update([
            'processing_status' => ProcessingStatusType::PENDING,
            'processing_error' => null,
            'processing_started_at' => now(),
            'processing_completed_at' => null,
        ]);

        $sourceType = $item->source_type;
        $processor = SourceProcessorFactory::create($sourceType);
        $processor->retry($item);

        return (new LibraryItemResource($item))
            ->response()
            ->setStatusCode(200);
    }

    /**
     * Redownload the media file for a library item.
     */
    public function redownload(int $id): JsonResponse
    {
        $item = Auth::user()->libraryItems()->findOrFail($id);

        if (! $item->mediaFile) {
            return response()->json(['message' => 'No media file associated with this library item.'], 422);
        }

        if (! $item->mediaFile->source_url) {
            return response()->json(['message' => 'Cannot redownload: no source URL available for this media file.'], 422);
        }

        if ($item->mediaFile->user_id !== $item->user_id) {
            return response()->json(['message' => 'Cannot redownload a media file owned by another user.'], 422);
        }

        $claimed = $item->newQuery()
            ->whereKey($item->id)
            ->where('processing_status', '!=', ProcessingStatusType::PROCESSING)
            ->update([
                'processing_status' => ProcessingStatusType::PROCESSING,
                'processing_started_at' => now(),
                'processing_completed_at' => null,
                'processing_error' => null,
            ]);

        if ($claimed === 0) {
            return response()->json(['message' => 'This media file is already being processed.'], 422);
        }

        $item->refresh();

        if ($item->source_type === 'youtube') {
            dispatch(new ProcessYouTubeAudio($item, $item->source_url));
        } else {
            dispatch(new RedownloadMediaFile($item));
        }

        return (new LibraryItemResource($item))
            ->response()
            ->setStatusCode(200);
    }
}
