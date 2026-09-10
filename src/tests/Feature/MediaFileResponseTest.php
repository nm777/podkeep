<?php

use App\Models\MediaFile;
use App\Models\User;
use Illuminate\Support\Facades\Storage;

beforeEach(function () {
    Storage::fake('media');

    $this->filePath = 'media/cacheable.mp3';
    $this->fileContent = '0123456789';

    Storage::disk('media')->put($this->filePath, $this->fileContent);
    $this->mediaFile = MediaFile::factory()->create([
        'file_path' => $this->filePath,
        'filesize' => strlen($this->fileContent),
        'mime_type' => 'audio/mpeg',
        'is_public' => true,
    ]);
});

it('serves public media with cache validators and honors conditional requests', function () {
    $response = $this->get("/files/{$this->filePath}")
        ->assertSuccessful()
        ->assertHeaderContains('Cache-Control', 'public')
        ->assertHeaderContains('Cache-Control', 'max-age=3600');

    $etag = (string) $response->headers->get('ETag');
    $lastModified = (string) $response->headers->get('Last-Modified');

    expect($etag)->not->toBeEmpty()
        ->and($lastModified)->not->toBeEmpty();

    $this->withHeader('If-None-Match', $etag)
        ->get("/files/{$this->filePath}")
        ->assertStatus(304);

    $this->flushHeaders()
        ->withHeader('If-Modified-Since', $lastModified)
        ->get("/files/{$this->filePath}")
        ->assertStatus(304);
});

it('does not make protected media publicly cacheable', function () {
    $user = User::factory()->create();
    $this->mediaFile->update(['user_id' => $user->id, 'is_public' => false]);

    $response = $this->actingAs($user)->get("/files/{$this->filePath}")->assertSuccessful();

    $cacheControl = (string) $response->headers->get('Cache-Control');

    expect($cacheControl)->toContain('private');
    expect($cacheControl)->not->toContain('public');
});

it('preserves byte range responses', function () {
    $this->withHeader('Range', 'bytes=2-5')
        ->get("/files/{$this->filePath}")
        ->assertStatus(206)
        ->assertHeader('Accept-Ranges', 'bytes')
        ->assertHeader('Content-Range', 'bytes 2-5/10')
        ->assertHeader('Content-Length', '4');
});
