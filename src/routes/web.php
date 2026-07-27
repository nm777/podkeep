<?php

use App\Http\Controllers\Admin\AdminQueueController;
use App\Http\Controllers\Admin\UserManagementController;
use App\Http\Controllers\ChapterController;
use App\Http\Controllers\FeedController;
use App\Http\Controllers\LibraryController;
use App\Http\Controllers\MediaController;
use App\Http\Controllers\RssController;
use App\Http\Controllers\ShareController;
use App\Http\Controllers\Web\UrlDuplicateCheckController;
use App\Http\Controllers\Web\YouTubeController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('', function () {
    return Inertia::render('welcome');
})->name('home');

Route::get('rss/{user_guid}/{feed_slug}', [RssController::class, 'show'])->name('rss.show')->middleware('throttle:120,1');

Route::get('share/{user_guid}/{feed_slug}', [ShareController::class, 'show'])->name('share.show')->middleware('throttle:120,1');

Route::get('files/{file_path}', [MediaController::class, 'show'])->name('files.show')->where('file_path', '.*')->middleware('throttle:60,1');

Route::post('check-url-duplicate', [UrlDuplicateCheckController::class, 'check'])
    ->middleware(['auth', 'verified', 'throttle:30,1']);
Route::get('youtube/video-info/{videoId}', [YouTubeController::class, 'getVideoInfo'])->middleware(['auth', 'verified', 'throttle:30,1']);

Route::middleware(['auth', 'verified', 'approved'])->group(function () {
    $dashboardData = function () {
        $feeds = Auth::user()->feeds()
            ->withCount('items')
            ->latest()
            ->get();
        $libraryItems = Auth::user()->libraryItems()
            ->with('mediaFile.chapters', 'feeds')
            ->latest()
            ->get();

        return [
            'feeds' => $feeds,
            'libraryItems' => $libraryItems,
        ];
    };

    Route::get('feeds', function () use ($dashboardData) {
        if (request()->expectsJson()) {
            return response()->json(Auth::user()->feeds()->latest()->get());
        }

        return Inertia::render('dashboard', array_merge($dashboardData(), ['activeTab' => 'feeds']));
    })->name('dashboard');

    Route::get('library', function () use ($dashboardData) {
        return Inertia::render('dashboard', array_merge($dashboardData(), ['activeTab' => 'library']));
    })->name('library.index');

    Route::resource('feeds', FeedController::class)->only(['store', 'update', 'destroy']);
    Route::get('feeds/{feed}/edit', [FeedController::class, 'edit'])->name('feeds.edit');

    Route::resource('library', LibraryController::class)->only(['store', 'update', 'destroy']);
    Route::put('library/{library_item}/chapters', [ChapterController::class, 'sync'])->name('library.chapters.sync');
    Route::post('library/{library_item}/chapters/generate', [ChapterController::class, 'generate'])->name('library.chapters.generate');
    Route::post('library', [LibraryController::class, 'store'])
        ->name('library.store')
        ->middleware('throttle:10,1');

    Route::post('library/{id}/retry', [LibraryController::class, 'retry'])
        ->name('library.retry')
        ->middleware('throttle:10,1');

    Route::post('library/{id}/redownload', [LibraryController::class, 'redownload'])
        ->name('library.redownload')
        ->middleware('throttle:10,1');

    Route::post('library/{id}/feeds', [LibraryController::class, 'attachFeed'])->name('library.feeds.attach');
});

Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('queue', [AdminQueueController::class, 'index'])->name('queue.index');
    Route::post('queue/{id}/cancel', [AdminQueueController::class, 'cancel'])->name('queue.cancel');
    Route::post('queue/{id}/release', [AdminQueueController::class, 'release'])->name('queue.release');
    Route::post('queue/failed/{uuid}/retry', [AdminQueueController::class, 'retry'])->name('queue.retry');
    Route::post('queue/failed/{uuid}/delete', [AdminQueueController::class, 'delete'])->name('queue.delete');

    Route::get('users', [UserManagementController::class, 'index'])->name('users.index');
    Route::post('users/{user}/approve', [UserManagementController::class, 'approve'])->name('users.approve');
    Route::post('users/{user}/reject', [UserManagementController::class, 'reject'])->name('users.reject');
    Route::post('users/{user}/toggle-admin', [UserManagementController::class, 'toggleAdmin'])->name('users.toggle-admin');
});

require __DIR__.'/settings.php';
require __DIR__.'/auth.php';
