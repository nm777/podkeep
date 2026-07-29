<?php

namespace App\Services\MediaProcessing;

use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class MediaDownloader
{
    /**
     * Download media from URL to a temp file on the public disk.
     * Streams to disk via Guzzle's sink option to avoid loading
     * large files into memory.
     *
     * @return string Relative path on the public disk
     */
    public function downloadFromUrl(string $url): string
    {
        $this->validateUrlSafety($url);

        try {
            return $this->downloadToTempFile($url);
        } catch (\Exception $e) {
            Log::error('Media download failed', [
                'url' => $url,
                'error' => $e->getMessage(),
            ]);

            throw $e;
        }
    }

    /**
     * Download URL to a temp file, handling HTML redirects recursively.
     */
    private function downloadToTempFile(string $url, int $redirectDepth = 0): string
    {
        $this->validateUrlSafety($url);

        if ($redirectDepth > 5) {
            throw new \Exception('Too many redirects');
        }

        $extension = pathinfo(parse_url($url, PHP_URL_PATH), PATHINFO_EXTENSION) ?: 'mp3';
        $relativePath = 'temp-downloads/'.uniqid().'.'.$extension;

        Storage::disk('public')->makeDirectory('temp-downloads');
        $absolutePath = Storage::disk('public')->path($relativePath);

        try {
            $response = $this->executeDownload($url, $absolutePath);

            if ($response->redirect() && $location = $response->header('Location')) {
                return $this->downloadToTempFile(
                    $this->makeAbsoluteUrl($location, $url),
                    $redirectDepth + 1,
                );
            }

            if (! $response->successful()) {
                throw new \Exception('Failed to download file: HTTP '.$response->status());
            }

            // Guzzle's sink option streams the body to disk in production.
            // Http::fake doesn't process sink, so write the body manually in tests.
            if (! file_exists($absolutePath) || filesize($absolutePath) === 0) {
                $body = $response->body();
                if (empty($body)) {
                    throw new \Exception('Downloaded file is empty');
                }
                file_put_contents($absolutePath, $body);
            }

            // Read only the first bytes for content validation
            $firstBytes = file_get_contents($absolutePath, false, null, 0, 4096);

            // Handle HTML redirects (redirect pages are small, safe to read fully)
            if ($this->isHtmlContent($firstBytes)) {
                $html = file_get_contents($absolutePath);
                Storage::disk('public')->delete($relativePath);

                $redirectUrl = $this->extractRedirectUrl($html, $url);
                if ($redirectUrl) {
                    return $this->downloadToTempFile($redirectUrl, $redirectDepth + 1);
                }

                throw new \Exception('Download failed: Got HTML content instead of media file');
            }

            $this->validateMediaContent($firstBytes);

            return $relativePath;
        } catch (\Exception $e) {
            Storage::disk('public')->delete($relativePath);

            throw $e;
        }
    }

    /**
     * Execute HTTP download, streaming response body to the sink path.
     */
    private function executeDownload(string $url, string $sinkPath): Response
    {
        return Http::timeout(60)->withOptions([
            'sink' => $sinkPath,
            'allow_redirects' => false,
        ])->get($url);
    }

    /**
     * Check if content is HTML.
     */
    private function isHtmlContent(string $content): bool
    {
        return str_starts_with($content, '<!DOCTYPE html') || str_starts_with($content, '<html');
    }

    /**
     * Extract redirect URL from JavaScript.
     */
    private function extractRedirectUrl(string $html, string $originalUrl): ?string
    {
        // Pattern 1: window.location.replace('url')
        if (preg_match('/window\.location\.replace\([\'"]([^\'"]+)[\'"]\)/', $html, $matches)) {
            return $this->makeAbsoluteUrl($matches[1], $originalUrl);
        }

        // Pattern 2: window.location.href.replace('pattern', 'replacement')
        if (preg_match('/window\.location\.href\.replace\([\'"]([^\'"]+)[\'"],\s*[\'"]([^\'"]+)[\'"]\)/', $html, $matches)) {
            $pattern = $matches[1];
            $replacement = $matches[2];
            $redirectUrl = str_replace($pattern, $replacement, $originalUrl);

            return $this->makeAbsoluteUrl($redirectUrl, $originalUrl);
        }

        return null;
    }

    /**
     * Convert relative URL to absolute.
     */
    private function makeAbsoluteUrl(string $url, string $baseUrl): string
    {
        if (str_starts_with($url, 'http')) {
            return $url;
        }

        $parsedUrl = parse_url($baseUrl);
        $schemeHost = $parsedUrl['scheme'].'://'.$parsedUrl['host'];

        if (str_starts_with($url, '/')) {
            return $schemeHost.$url;
        }

        $path = dirname($parsedUrl['path']);

        return $schemeHost.$path.'/'.$url;
    }

    /**
     * Validate that content is valid media.
     */
    private function validateMediaContent(string $content): void
    {
        $validMediaSignatures = [
            'RIFF' => true, // WAV/AVI
            'OggS' => true, // OGG
            'fLaC' => true, // FLAC
            'MP4' => true,  // M4A/MP4
            "\xFF\xFB" => true, // MP3
            "\xFF\xF3" => true, // MP3
            "\xFF\xF2" => true, // MP3
            "\x1A\x45\xDF\xA3" => true, // WebM/Matroska
        ];

        $fileSignature = substr($content, 0, 4);
        $isValidMedia = isset($validMediaSignatures[$fileSignature]) ||
                       isset($validMediaSignatures[substr($content, 0, 2)]) ||
                       str_starts_with($fileSignature, 'ID3'); // MP3 with ID3 tag

        // Check for MP4/M4V (ftyp box at byte offset 4)
        if (! $isValidMedia && strlen($content) >= 8) {
            $isValidMedia = substr($content, 4, 4) === 'ftyp';
        }

        if (! $isValidMedia) {
            throw new \Exception('Content does not appear to be a valid media file');
        }
    }

    private function validateUrlSafety(string $url): void
    {
        $parsed = parse_url($url);

        if (! $parsed || ! isset($parsed['host'])) {
            throw new \InvalidArgumentException('Invalid URL: no host');
        }

        if (! isset($parsed['scheme']) || ! in_array($parsed['scheme'], ['http', 'https'], true)) {
            throw new \InvalidArgumentException('Invalid URL: only http and https schemes are allowed');
        }

        $host = $parsed['host'];

        if (filter_var($host, FILTER_VALIDATE_IP)) {
            $ip = $host;
        } else {
            $ip = gethostbyname($host);
            if ($ip === $host) {
                throw new \InvalidArgumentException('Invalid URL: could not resolve host');
            }
        }

        $blockedRanges = [
            '0.0.0.0/8',
            '10.0.0.0/8',
            '100.64.0.0/10',
            '127.0.0.0/8',
            '169.254.0.0/16',
            '172.16.0.0/12',
            '192.0.0.0/24',
            '192.0.2.0/24',
            '192.168.0.0/16',
            '198.18.0.0/15',
            '198.51.100.0/24',
            '203.0.113.0/24',
            '224.0.0.0/4',
            '240.0.0.0/4',
        ];

        $ipLong = ip2long($ip);
        if ($ipLong === false) {
            throw new \InvalidArgumentException('Invalid URL: could not resolve host');
        }

        foreach ($blockedRanges as $range) {
            [$subnet, $bits] = explode('/', $range);
            $subnetLong = ip2long($subnet);
            $mask = ~((1 << (32 - (int) $bits)) - 1);

            if (($ipLong & $mask) === ($subnetLong & $mask)) {
                throw new \InvalidArgumentException('URL resolves to a private/internal IP address');
            }
        }
    }
}
