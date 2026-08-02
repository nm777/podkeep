<?php

use Illuminate\Support\Facades\Storage;

it('relocates public media and pending-job temporary files to private storage', function () {
    Storage::fake('public');
    Storage::fake('media');

    Storage::disk('public')->put('media/episode.mp3', 'episode');
    Storage::disk('public')->put('temp-uploads/pending.mp3', 'upload');
    Storage::disk('public')->put('temp-youtube/download.mp3', 'youtube');
    Storage::disk('public')->put('temp-downloads/remote.mp3', 'download');

    $this->artisan('media:relocate-public-storage')->assertSuccessful();

    Storage::disk('media')->assertExists([
        'media/episode.mp3',
        'temp-uploads/pending.mp3',
        'temp-youtube/download.mp3',
        'temp-downloads/remote.mp3',
    ]);
    Storage::disk('public')->assertMissing([
        'media/episode.mp3',
        'temp-uploads/pending.mp3',
        'temp-youtube/download.mp3',
        'temp-downloads/remote.mp3',
    ]);

    $this->artisan('media:relocate-public-storage')->assertSuccessful();
});

it('preserves public media when the private destination already exists', function () {
    Storage::fake('public');
    Storage::fake('media');

    Storage::disk('public')->put('media/episode.mp3', 'public copy');
    Storage::disk('media')->put('media/episode.mp3', 'private copy');

    $this->artisan('media:relocate-public-storage')->assertSuccessful();

    expect(Storage::disk('media')->get('media/episode.mp3'))->toBe('private copy')
        ->and(Storage::disk('public')->get('media/episode.mp3'))->toBe('public copy');
});
