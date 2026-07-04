<?php

use App\Http\Controllers\Api\V1\FeedController;
use App\Http\Controllers\Api\V1\LibraryItemController;
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

        Route::apiResource('feeds', FeedController::class);
        Route::apiResource('library', LibraryItemController::class);
    });
