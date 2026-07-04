<?php

use App\Http\Controllers\Api\V1\FeedController;
use App\Http\Controllers\Api\V1\FeedItemController;
use App\Http\Controllers\Api\V1\LibraryItemController;
use App\Http\Controllers\Api\V1\MediaProcessingController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::middleware(['throttle:api', 'auth:sanctum', 'eligible.api'])
    ->prefix('v1')
    ->group(function () {
        Route::get('/me', function (Request $request) {
            return response()->json([
                'id' => $request->user()->id,
                'name' => $request->user()->name,
                'email' => $request->user()->email,
            ]);
        })->name('api.v1.me');

        Route::get('feeds/{feed}/items', [FeedItemController::class, 'index'])->name('api.v1.feed-items.index');
        Route::post('feeds/{feed}/items', [FeedItemController::class, 'store'])->name('api.v1.feed-items.store');
        Route::put('feeds/{feed}/items/reorder', [FeedItemController::class, 'reorder'])->name('api.v1.feed-items.reorder');
        Route::delete('feeds/{feed}/items/{item}', [FeedItemController::class, 'destroy'])->name('api.v1.feed-items.destroy');

        Route::apiResource('feeds', FeedController::class);
        Route::apiResource('library', LibraryItemController::class);
        Route::post('library/{id}/retry', [MediaProcessingController::class, 'retry'])->name('api.v1.library.retry');
        Route::post('library/{id}/redownload', [MediaProcessingController::class, 'redownload'])->name('api.v1.library.redownload');
    });
