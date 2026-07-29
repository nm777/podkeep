<?php

use App\Services\MediaProcessing\MediaDownloader;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

uses(TestCase::class);

beforeEach(function () {
    Storage::fake('public');
});

test('downloads media file successfully', function () {
    $url = 'https://example.com/audio.mp3';
    $content = 'ID3'.str_repeat("\x00", 100).'audio';

    Http::fake([$url => Http::response($content, 200)]);

    $path = (new MediaDownloader)->downloadFromUrl($url);

    expect(Storage::disk('public')->get($path))->toBe($content);
});

test('pins a download to its validated address', function () {
    $downloader = new class extends MediaDownloader
    {
        /** @return array<string, mixed> */
        public function options(string $url, string $sinkPath, string $ip): array
        {
            return $this->downloadOptions($url, $sinkPath, $ip);
        }
    };

    $options = $downloader->options('https://media.example.com:8443/audio.mp3', '/tmp/audio.mp3', '93.184.216.34');

    expect($options['curl'][CURLOPT_RESOLVE])->toBe(['media.example.com:8443:93.184.216.34']);
});

test('throws exception for failed http request', function () {
    $url = 'https://example.com/audio.mp3';

    Http::fake([$url => Http::response('Not Found', 404)]);

    expect(fn () => (new MediaDownloader)->downloadFromUrl($url))
        ->toThrow(Exception::class, 'Failed to download file: HTTP 404');
});

test('throws exception for empty content', function () {
    $url = 'https://example.com/audio.mp3';

    Http::fake([$url => Http::response('', 200)]);

    expect(fn () => (new MediaDownloader)->downloadFromUrl($url))
        ->toThrow(Exception::class, 'Downloaded file is empty');
});

test('throws exception for html content without redirect', function () {
    $url = 'https://example.com/audio.mp3';

    Http::fake([$url => Http::response('<!DOCTYPE html><html><body>Error</body></html>', 200)]);

    expect(fn () => (new MediaDownloader)->downloadFromUrl($url))
        ->toThrow(Exception::class, 'Download failed: Got HTML content instead of media file');
});

test('handles javascript redirect', function () {
    $url = 'https://example.com/download';
    $redirectUrl = 'https://example.com/actual.mp3';
    $audioContent = 'ID3'.str_repeat("\x00", 100).'audio';

    Http::fake([
        $url => Http::response('<html><script>window.location.replace("'.$redirectUrl.'")</script></html>', 200),
        $redirectUrl => Http::response($audioContent, 200),
    ]);

    $path = (new MediaDownloader)->downloadFromUrl($url);

    expect(Storage::disk('public')->get($path))->toBe($audioContent);
});

test('handles HTTP redirect without retaining the redirect sink', function () {
    $url = 'https://example.com/download';
    $redirectUrl = 'https://example.com/actual.mp3';
    $audioContent = 'ID3'.str_repeat("\x00", 100).'audio';

    Http::fake([
        $url => Http::response('', 302, ['Location' => $redirectUrl]),
        $redirectUrl => Http::response($audioContent, 200),
    ]);

    $path = (new MediaDownloader)->downloadFromUrl($url);

    expect(Storage::disk('public')->get($path))->toBe($audioContent)
        ->and(Storage::disk('public')->allFiles('temp-downloads'))->toBe([$path]);
});

test('converts relative redirect url to absolute', function () {
    $url = 'https://example.com/download';
    $absoluteUrl = 'https://example.com/files/audio.mp3';
    $audioContent = 'ID3'.str_repeat("\x00", 100).'audio';

    Http::fake([
        $url => Http::response('<html><script>window.location.replace("/files/audio.mp3")</script></html>', 200),
        $absoluteUrl => Http::response($audioContent, 200),
    ]);

    $path = (new MediaDownloader)->downloadFromUrl($url);

    expect(Storage::disk('public')->get($path))->toBe($audioContent);
});

test('validates mp3 with id3 tag', function () {
    $url = 'https://example.com/audio.mp3';
    $content = 'ID3'.str_repeat("\0", 1000).'audio';

    Http::fake([$url => Http::response($content, 200)]);

    $path = (new MediaDownloader)->downloadFromUrl($url);

    expect(Storage::disk('public')->get($path))->toBe($content);
});

test('throws exception for invalid media content', function () {
    $url = 'https://example.com/file.txt';

    Http::fake([$url => Http::response(str_repeat('invalid text content', 100), 200)]);

    expect(fn () => (new MediaDownloader)->downloadFromUrl($url))
        ->toThrow(Exception::class);
});

test('rejects a declared download larger than the configured limit', function () {
    config(['constants.media.max_bytes' => 16]);
    $url = 'https://example.com/audio.mp3';

    Http::fake([$url => Http::response('ID3', 200, ['Content-Length' => 17])]);

    expect(fn () => (new MediaDownloader)->downloadFromUrl($url))
        ->toThrow(RuntimeException::class, 'Download exceeds the maximum allowed file size');

    expect(Storage::disk('public')->allFiles('temp-downloads'))->toBeEmpty();
});

test('rejects a streamed download that exceeds the configured limit', function () {
    config(['constants.media.max_bytes' => 16]);
    $url = 'https://example.com/audio.mp3';

    Http::fake([$url => Http::response('ID3'.str_repeat("\x00", 14), 200, ['Content-Length' => 16])]);

    expect(fn () => (new MediaDownloader)->downloadFromUrl($url))
        ->toThrow(RuntimeException::class, 'Download exceeds the maximum allowed file size');

    expect(Storage::disk('public')->allFiles('temp-downloads'))->toBeEmpty();
});

test('downloads media at the configured limit', function () {
    config(['constants.media.max_bytes' => 16]);
    $url = 'https://example.com/audio.mp3';
    $content = 'ID3'.str_repeat("\x00", 13);

    Http::fake([$url => Http::response($content, 200, ['Content-Length' => 16])]);

    $path = (new MediaDownloader)->downloadFromUrl($url);

    expect(Storage::disk('public')->get($path))->toBe($content);
});
