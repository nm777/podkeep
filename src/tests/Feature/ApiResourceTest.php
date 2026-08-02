<?php

use App\Http\Resources\FeedResource;
use App\Http\Resources\LibraryItemResource;
use App\Http\Resources\MediaFileResource;
use App\Models\Feed;
use App\Models\LibraryItem;
use App\Models\MediaFile;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\MissingValue;

describe('API Resources', function () {
    beforeEach(function () {
        $this->user = User::factory()->create();
    });

    it('transforms library item correctly', function () {
        $libraryItem = LibraryItem::factory()->create([
            'user_id' => $this->user->id,
            'title' => 'Test Item',
            'description' => 'Test Description',
            'source_type' => 'url',
            'source_url' => 'https://example.com/test.mp3',
        ]);

        $resource = LibraryItemResource::make($libraryItem);
        $array = $resource->toArray(request());

        expect($array)->toHaveKey('id');
        expect($array)->toHaveKey('title');
        expect($array)->toHaveKey('description');
        expect($array)->toHaveKey('source_type');
        expect($array)->toHaveKey('source_url');
        expect($array)->toHaveKey('is_duplicate');
        expect($array)->toHaveKey('processing_status');
        expect($array)->toHaveKey('is_processing');
        expect($array['title'])->toBe('Test Item');
        expect($array['source_type'])->toBe('url');
    });

    it('transforms feed correctly', function () {
        $feed = Feed::factory()->create([
            'user_id' => $this->user->id,
            'title' => 'Test Feed',
            'description' => 'Test Feed Description',
            'is_public' => true,
            'slug' => 'test-feed',
        ]);

        $resource = FeedResource::make($feed);
        $array = $resource->toArray(request());

        expect($array)->toHaveKey('id');
        expect($array)->toHaveKey('title');
        expect($array)->toHaveKey('description');
        expect($array)->toHaveKey('is_public');
        expect($array)->toHaveKey('slug');
        expect($array['title'])->toBe('Test Feed');
        expect($array['is_public'])->toBeTrue();
    });

    it('transforms media file correctly', function () {
        $mediaFile = MediaFile::factory()->create([
            'user_id' => $this->user->id,
            'file_path' => 'test/path.mp3',
            'file_hash' => 'abc123',
            'mime_type' => 'audio/mpeg',
            'filesize' => 1024,
            'transcript' => [['start' => 0, 'end' => 60, 'text' => 'Opening prayer']],
            'chapter_generation_status' => 'processing',
            'chapter_generation_error' => 'An earlier attempt failed',
        ]);

        $resource = MediaFileResource::make($mediaFile);
        $request = Request::create('/');
        $request->setUserResolver(fn () => $this->user);
        $array = $resource->toArray($request);

        expect($array)->toHaveKey('id');
        expect($array)->not->toHaveKey('file_path');
        expect($array)->toHaveKey('public_url');
        expect($array)->toHaveKey('file_hash');
        expect($array)->toHaveKey('mime_type');
        expect($array)->toHaveKey('filesize');
        expect($array)->toHaveKey('transcript');
        expect($array)->toHaveKey('chapter_generation_status');
        expect($array)->toHaveKey('chapter_generation_error');
        expect($array['mime_type'])->toBe('audio/mpeg');
        expect($array['filesize'])->toBe(1024);
        expect($array['transcript'])->toBe([['start' => 0, 'end' => 60, 'text' => 'Opening prayer']]);
        expect($array['chapter_generation_status'])->toBe('processing');
        expect($array['chapter_generation_error'])->toBe('An earlier attempt failed');
    });

    it('only exposes chapter generation details to the media owner', function () {
        $mediaFile = MediaFile::factory()->create([
            'user_id' => $this->user->id,
            'transcript' => [['start' => 0, 'end' => 60, 'text' => 'Opening prayer']],
            'chapter_generation_status' => 'processing',
            'chapter_generation_error' => 'An earlier attempt failed',
        ]);
        $request = Request::create('/');
        $request->setUserResolver(fn () => User::factory()->create());

        $array = MediaFileResource::make($mediaFile)->resolve($request);

        expect(array_intersect([
            'transcript',
            'chapter_generation_status',
            'chapter_generation_error',
        ], array_keys($array)))->toBeEmpty();
    });

    it('handles null relationships correctly', function () {
        $libraryItem = LibraryItem::factory()->create([
            'user_id' => $this->user->id,
            'title' => 'Test Item',
            'media_file_id' => null, // No associated media file
        ]);

        $resource = LibraryItemResource::make($libraryItem);
        $array = $resource->toArray(request());

        // The when() method should handle null relationships gracefully
        // MissingValue objects are converted to null during JSON serialization
        expect($array)->toHaveKey('media_file');
        expect($array['media_file'])->toBeInstanceOf(MissingValue::class);
    });
});
