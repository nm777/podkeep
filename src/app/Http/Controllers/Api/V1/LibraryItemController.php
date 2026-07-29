<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\StoreLibraryItemRequest;
use App\Http\Requests\Api\V1\UpdateLibraryItemRequest;
use App\Http\Resources\LibraryItemResource;
use App\Services\MediaFileRetirementService;
use App\Services\SourceProcessors\SourceProcessorFactory;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;

class LibraryItemController extends Controller
{
    /**
     * Display a listing of the user's library items.
     */
    public function index(): JsonResponse
    {
        $libraryItems = Auth::user()->libraryItems()
            ->with('mediaFile', 'feeds')
            ->latest()
            ->get();

        return LibraryItemResource::collection($libraryItems)
            ->response()
            ->setStatusCode(200);
    }

    /**
     * Store a newly created library item.
     */
    public function store(StoreLibraryItemRequest $request): JsonResponse
    {
        $validated = $request->validated();

        $sourceType = $request->input('source_type', $request->hasFile('file') ? 'upload' : 'url');
        $sourceUrl = $request->input('source_url', $request->input('url'));

        $processor = SourceProcessorFactory::create($sourceType);
        [$libraryItem, $message] = $processor->process($request, $validated, $sourceType, $sourceUrl);

        return (new LibraryItemResource($libraryItem))
            ->response()
            ->setStatusCode(201);
    }

    /**
     * Display the specified library item.
     */
    public function show(int $id): JsonResponse
    {
        $libraryItem = Auth::user()->libraryItems()
            ->with('mediaFile')
            ->findOrFail($id);

        return (new LibraryItemResource($libraryItem))
            ->response()
            ->setStatusCode(200);
    }

    /**
     * Update the specified library item.
     */
    public function update(UpdateLibraryItemRequest $request, int $id): JsonResponse
    {
        $libraryItem = Auth::user()->libraryItems()->findOrFail($id);

        $validated = $request->validated();

        $libraryItem->update($validated);

        foreach ($libraryItem->feedItems()->pluck('feed_id') as $feedId) {
            Cache::forget("rss.{$feedId}");
        }

        return (new LibraryItemResource($libraryItem))
            ->response()
            ->setStatusCode(200);
    }

    /**
     * Remove the specified library item.
     */
    public function destroy(int $id): JsonResponse
    {
        $libraryItem = Auth::user()->libraryItems()->findOrFail($id);

        $mediaFile = $libraryItem->mediaFile;

        $feedIds = $libraryItem->feedItems()->pluck('feed_id');

        $libraryItem->delete();

        if ($mediaFile) {
            MediaFileRetirementService::retire($mediaFile);
        }

        foreach ($feedIds as $feedId) {
            Cache::forget("rss.{$feedId}");
        }

        return response()->json(null, 204);
    }
}
