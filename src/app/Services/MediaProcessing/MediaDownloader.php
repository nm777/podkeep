<?php

namespace App\Services\MediaProcessing;

use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class MediaDownloader
{
    /**
     * Download media from URL and stream to a temp file.
     * Returns the storage path of the downloaded temp file.
     */
    public function downloadFromUrl(string $url, int $maxRedirects = 5): string
    {
        try {
            $tempPath = $this->streamToTempFile($url);

            $disk = Storage::disk('public');
            $fullPath = $disk->path($tempPath);
            $header = file_get_contents($fullPath, false, null, 0, 4096);

            if (empty($header)) {
                $disk->delete($tempPath);
                throw new \Exception('Downloaded file is empty');
            }

            $resolvedPath = $this->handleHtmlRedirect($header, $url, $tempPath, $maxRedirects);
            $finalPath = $resolvedPath ?? $tempPath;

            $finalFullPath = $disk->path($finalPath);
            $finalHeader = file_get_contents($finalFullPath, false, null, 0, 4096);
            $this->validateMediaContent($finalHeader);

            return $finalPath;
        } catch (\Exception $e) {
            Log::error('Media download failed', [
                'url' => $url,
                'error' => $e->getMessage(),
            ]);

            throw $e;
        }
    }

    /**
     * Stream download directly to a temp file on disk.
     */
    private function streamToTempFile(string $url): string
    {
        $tempPath = 'temp-downloads/'.uniqid().'_'.basename(parse_url($url, PHP_URL_PATH) ?: 'download');

        $disk = Storage::disk('public');
        $directory = dirname($tempPath);
        if (! $disk->directoryExists($directory)) {
            $disk->makeDirectory($directory);
        }

        $sinkPath = $disk->path($tempPath);

        $response = Http::timeout(60)->withOptions([
            'allow_redirects' => [
                'max' => 5,
                'strict' => true,
                'referer' => true,
                'protocols' => ['http', 'https'],
                'track_redirects' => true,
            ],
            'sink' => $sinkPath,
        ])->get($url);

        if (! $response->successful()) {
            $disk->delete($tempPath);
            throw new \Exception('Failed to download file: HTTP '.$response->status());
        }

        return $tempPath;
    }

    /**
     * Handle HTML JavaScript redirects.
     */
    private function handleHtmlRedirect(string $header, string $originalUrl, string $currentTempPath, int $maxRedirects): ?string
    {
        if (! $this->isHtmlContent($header)) {
            return null;
        }

        $disk = Storage::disk('public');

        if ($maxRedirects <= 0) {
            $disk->delete($currentTempPath);
            throw new \Exception('Download failed: Maximum HTML redirect limit reached');
        }

        $redirectUrl = $this->extractRedirectUrl($header, $originalUrl);

        if ($redirectUrl) {
            $disk->delete($currentTempPath);
            try {
                return $this->downloadFromUrl($redirectUrl, $maxRedirects - 1);
            } catch (\Exception $e) {
                if (str_contains($e->getMessage(), 'Maximum HTML redirect limit')) {
                    throw $e;
                }
                if (str_contains($e->getMessage(), 'Got HTML redirect page')) {
                    throw $e;
                }
                throw new \Exception('Download failed: Got HTML redirect page instead of media file');
            }
        }

        $disk->delete($currentTempPath);
        throw new \Exception('Download failed: Got HTML content instead of media file');
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
        if (preg_match('/window\.location\.replace\([\'"]([^\'"]+)[\'"]\)/', $html, $matches)) {
            return $this->makeAbsoluteUrl($matches[1], $originalUrl);
        }

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
     * Validate that content is valid media based on file signature.
     */
    private function validateMediaContent(string $header): void
    {
        $validMediaSignatures = [
            'RIFF' => true,
            'OggS' => true,
            'fLaC' => true,
            'MP4' => true,
            "\xFF\xFB" => true,
            "\xFF\xF3" => true,
            "\xFF\xF2" => true,
        ];

        $fileSignature = substr($header, 0, 4);
        $isValidMedia = isset($validMediaSignatures[$fileSignature]) ||
                       isset($validMediaSignatures[substr($header, 0, 2)]) ||
                       str_starts_with($fileSignature, 'ID3');

        if (! $isValidMedia && strlen($header) > 100) {
            throw new \Exception('Content does not appear to be a valid audio file');
        }
    }
}
