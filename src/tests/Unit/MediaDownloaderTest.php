<?php

use App\Services\MediaProcessing\MediaDownloader;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;

uses()->group('Unit', 'MediaDownloader');

test('downloads media file successfully', function () {
    Storage::fake('public');
    
    $url = 'https://example.com/audio.mp3';
    $content = 'fake audio content';

    Http::fake([$url => Http::response($content, 200)]);

    $result = MediaDownloader::downloadFromUrl($url);

    expect($result)->toBe($content);
});

test('throws exception for failed http request', function () {
    Storage::fake('public');
    
    $url = 'https://example.com/audio.mp3';

    Http::fake([$url => Http::response('Not Found', 404)]);

    expect(fn () => MediaDownloader::downloadFromUrl($url))
        ->toThrow(\Exception::class, 'Failed to download file: HTTP 404');
});

test('throws exception for empty content', function () {
    Storage::fake('public');
    
    $url = 'https://example.com/audio.mp3';

    Http::fake([$url => Http::response('', 200)]);

    expect(fn () => MediaDownloader::downloadFromUrl($url))
        ->toThrow(\Exception::class, 'Downloaded file is empty');
});

test('throws exception for html content without redirect', function () {
    Storage::fake('public');
    
    $url = 'https://example.com/audio.mp3';
    $htmlContent = '<!DOCTYPE html><html><body>Error page</body></html>';

    Http::fake([$url => Http::response($htmlContent, 200)]);

    expect(fn () => MediaDownloader::downloadFromUrl($url))
        ->toThrow(\Exception::class, 'Download failed: Got HTML content instead of media file');
});

test('handles javascript redirect with window.location.replace', function () {
    Storage::fake('public');
    
    $url = 'https://example.com/download';
    $redirectUrl = 'https://example.com/actual-audio.mp3';
    $htmlContent = '<html><script>window.location.replace("'.$redirectUrl.'")</script></html>';
    $audioContent = 'fake audio content';

    Http::fake([
        $url => Http::response($htmlContent, 200),
        $redirectUrl => Http::response($audioContent, 200),
    ]);

    $result = MediaDownloader::downloadFromUrl($url);

    expect($result)->toBe($audioContent);
});

test('throws exception when redirect fails', function () {
    Storage::fake('public');
    
    $url = 'https://example.com/download';
    $htmlContent = '<!DOCTYPE html><html><body>Redirect page</body></html>';

    Http::fake([$url => Http::response($htmlContent, 200)]);

    expect(fn () => MediaDownloader::downloadFromUrl($url))
        ->toThrow(\Exception::class, 'Download failed: Got HTML redirect page instead of media file');
});

test('converts relative url to absolute url with scheme and host', function () {
    Storage::fake('public');
    
    $baseUrl = 'https://example.com/page/download';
    $relativeUrl = 'files/audio.mp3';
    $fullUrl = 'https://example.com/files/audio.mp3';

    $htmlContent = '<!DOCTYPE html><html><script>window.location.replace("'.$relativeUrl.'")</script></html>';
    $audioContent = 'fake audio content';

    Http::fake([
        $baseUrl => Http::response($htmlContent, 200),
        $fullUrl => Http::response($audioContent, 200),
    ]);

    $result = MediaDownloader::downloadFromUrl($baseUrl);

    expect($result)->toBe($audioContent);
});

test('converts relative url with leading slash', function () {
    Storage::fake('public');
    
    $baseUrl = 'https://example.com/page/download';
    $relativeUrl = '/files/audio.mp3';
    $fullUrl = 'https://example.com/files/audio.mp3';

    $htmlContent = '<html><script>window.location.replace("'.$relativeUrl.'")</script></html>';
    $audioContent = 'fake audio content';

    Http::fake([
        $baseUrl => Http::response($htmlContent, 200),
        $fullUrl => Http::response($audioContent, 200),
    ]);

    $result = MediaDownloader::downloadFromUrl($baseUrl);

    expect($result)->toBe($audioContent);
});

test('validates media content signature', function () {
    Storage::fake('public');
    
    $url = 'https://example.com/audio.mp3';
    
    // Valid MP3 with ID3 tag
    $content = 'ID3'.str_repeat("\0", 1000).'fake audio content';

    Http::fake([$url => Http::response($content, 200)]);

    $result = MediaDownloader::downloadFromUrl($url);

    expect($result)->toBe($content);
});

test('throws exception for invalid media content', function () {
    Storage::fake('public');
    
    $url = 'https://example.com/file.txt';
    $invalidContent = str_repeat('invalid text content', 100);

    Http::fake([$url => Http::response($invalidContent, 200)]);

    expect(fn () => MediaDownloader::downloadFromUrl($url))
        ->toThrow(\Exception::class);
});

test('allows short valid media content', function () {
    Storage::fake('public');
    
    $url = 'https://example.com/audio.mp3';
    
    // Valid MP3 signature (small file)
    $content = "\xFF\xFB".str_repeat("\0", 50);

    Http::fake([$url => Http::response($content, 200)]);

    $result = MediaDownloader::downloadFromUrl($url);

    expect($result)->toBe($content);
});

test('handles timeout errors gracefully', function () {
    Storage::fake('public');
    
    $url = 'https://example.com/audio.mp3';

    Http::fake([$url => Http::response('', 200)->timeout(60)]);

    expect(fn () => MediaDownloader::downloadFromUrl($url))
        ->toThrow(\Exception::class);
});


test('downloads media file successfully', function () {
    $url = 'https://example.com/audio.mp3';
    $content = 'fake audio content';

    Http::fake([$url => Http::response($content, 200)]);

    $result = MediaDownloader::downloadFromUrl($url);

    expect($result)->toBe($content);
});

test('throws exception for failed http request', function () {
    $url = 'https://example.com/audio.mp3';

    Http::fake([$url => Http::response('Not Found', 404)]);

    expect(fn () => MediaDownloader::downloadFromUrl($url))
        ->toThrow(\Exception::class, 'Failed to download file: HTTP 404');
});

test('throws exception for empty content', function () {
    $url = 'https://example.com/audio.mp3';

    Http::fake([$url => Http::response('', 200)]);

    expect(fn () => MediaDownloader::downloadFromUrl($url))
        ->toThrow(\Exception::class, 'Downloaded file is empty');
});

test('throws exception for html content without redirect', function () {
    $url = 'https://example.com/audio.mp3';
    $htmlContent = '<!DOCTYPE html><html><body>Error page</body></html>';

    Http::fake([$url => Http::response($htmlContent, 200)]);

    expect(fn () => MediaDownloader::downloadFromUrl($url))
        ->toThrow(\Exception::class, 'Download failed: Got HTML content instead of media file');
});

test('handles javascript redirect with window.location.replace', function () {
    $url = 'https://example.com/download';
    $redirectUrl = 'https://example.com/actual-audio.mp3';
    $htmlContent = '<html><script>window.location.replace("'.$redirectUrl.'")</script></html>';
    $audioContent = 'fake audio content';

    Http::fake([
        $url => Http::response($htmlContent, 200),
        $redirectUrl => Http::response($audioContent, 200),
    ]);

    $result = MediaDownloader::downloadFromUrl($url);

    expect($result)->toBe($audioContent);
});

test('throws exception when redirect fails', function () {
    $url = 'https://example.com/download';
    $htmlContent = '<!DOCTYPE html><html><body>Redirect page</body></html>';

    Http::fake([$url => Http::response($htmlContent, 200)]);

    expect(fn () => MediaDownloader::downloadFromUrl($url))
        ->toThrow(\Exception::class, 'Download failed: Got HTML redirect page instead of media file');
});

test('converts relative url to absolute url with scheme and host', function () {
    $baseUrl = 'https://example.com/page/download';
    $relativeUrl = 'files/audio.mp3';
    $fullUrl = 'https://example.com/files/audio.mp3';

    $htmlContent = '<!DOCTYPE html><html><script>window.location.replace("'.$relativeUrl.'")</script></html>';
    $audioContent = 'fake audio content';

    Http::fake([
        $baseUrl => Http::response($htmlContent, 200),
        $fullUrl => Http::response($audioContent, 200),
    ]);

    $result = MediaDownloader::downloadFromUrl($baseUrl);

    expect($result)->toBe($audioContent);
});

test('converts relative url with leading slash', function () {
    $baseUrl = 'https://example.com/page/download';
    $relativeUrl = '/files/audio.mp3';
    $fullUrl = 'https://example.com/files/audio.mp3';

    $htmlContent = '<html><script>window.location.replace("'.$relativeUrl.'")</script></html>';
    $audioContent = 'fake audio content';

    Http::fake([
        $baseUrl => Http::response($htmlContent, 200),
        $fullUrl => Http::response($audioContent, 200),
    ]);

    $result = MediaDownloader::downloadFromUrl($baseUrl);

    expect($result)->toBe($audioContent);
});

test('validates media content signature', function () {
    $url = 'https://example.com/audio.mp3';
    
    // Valid MP3 with ID3 tag
    $content = 'ID3'.str_repeat("\0", 1000).'fake audio content';

    Http::fake([$url => Http::response($content, 200)]);

    $result = MediaDownloader::downloadFromUrl($url);

    expect($result)->toBe($content);
});

test('throws exception for invalid media content', function () {
    $url = 'https://example.com/file.txt';
    $invalidContent = str_repeat('invalid text content', 100);

    Http::fake([$url => Http::response($invalidContent, 200)]);

    expect(fn () => MediaDownloader::downloadFromUrl($url))
        ->toThrow(\Exception::class);
});

test('allows short valid media content', function () {
    $url = 'https://example.com/audio.mp3';
    
    // Valid MP3 signature (small file)
    $content = "\xFF\xFB".str_repeat("\0", 50);

    Http::fake([$url => Http::response($content, 200)]);

    $result = MediaDownloader::downloadFromUrl($url);

    expect($result)->toBe($content);
});

test('handles timeout errors gracefully', function () {
    $url = 'https://example.com/audio.mp3';

    Http::fake([$url => Http::response('', 200)->timeout(60)]);

    expect(fn () => MediaDownloader::downloadFromUrl($url))
        ->toThrow(\Exception::class);
});

