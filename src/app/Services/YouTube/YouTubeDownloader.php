<?php

namespace App\Services\YouTube;

use App\Services\YouTubeUrlValidator;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;

class YouTubeDownloader
{
    /**
     * Download audio from YouTube URL using yt-dlp.
     */
    public function downloadAudio(string $youtubeUrl, string $tempDir): ?string
    {
        $tempPath = $tempDir.'/audio.%(ext)s';
        $videoId = YouTubeUrlValidator::extractVideoId($youtubeUrl);

        try {
            // Create temp directory
            Storage::disk('public')->makeDirectory($tempDir);

            // Download audio using yt-dlp
            $command = [
                'yt-dlp',
                '--extract-audio',
                '--audio-format',
                'mp3',
                '--audio-quality',
                '0', // best quality
                '--no-playlist',
                '--output',
                Storage::disk('public')->path($tempPath),
                $youtubeUrl,
            ];

            $process = new Process($command);
            $process->setTimeout(300); // 5 minutes timeout
            $process->run();

            Log::info('yt-dlp command completed', [
                'video_id' => $videoId,
                'exit_code' => $process->getExitCode(),
            ]);

            if (! $process->isSuccessful()) {
                Log::error('yt-dlp command failed', [
                    'video_id' => $videoId,
                    'exit_code' => $process->getExitCode(),
                    'error' => 'Audio download command failed',
                ]);
                throw new ProcessFailedException($process);
            }

            // Find downloaded file (yt-dlp might create different extensions)
            $downloadedFile = $this->findDownloadedFile($tempDir);

            if (! $downloadedFile || ! Storage::disk('public')->exists($downloadedFile)) {
                Log::error('No downloaded file found', [
                    'video_id' => $videoId,
                    'error' => 'Downloaded audio file not found',
                ]);

                return null;
            }

            Log::info('Found downloaded file', [
                'video_id' => $videoId,
            ]);

            return $downloadedFile;

        } catch (\Exception $e) {
            Log::error('YouTube download failed', [
                'video_id' => $videoId,
                'error' => 'Audio download failed',
            ]);

            throw $e;
        }
    }

    public function downloadVideo(string $youtubeUrl, string $tempDir): ?string
    {
        $tempPath = $tempDir.'/video.%(ext)s';
        $videoId = YouTubeUrlValidator::extractVideoId($youtubeUrl);

        try {
            Storage::disk('public')->makeDirectory($tempDir);

            $command = [
                'yt-dlp',
                '--format', 'bestvideo[ext=mp4]+bestaudio[ext=m4a]/mp4/best',
                '--no-playlist',
                '--output', Storage::disk('public')->path($tempPath),
                $youtubeUrl,
            ];

            $process = new Process($command);
            $process->setTimeout(300);
            $process->run();

            if (! $process->isSuccessful()) {
                Log::error('yt-dlp video download failed', [
                    'video_id' => $videoId,
                    'exit_code' => $process->getExitCode(),
                    'error' => 'Video download command failed',
                ]);

                return null;
            }

            $downloadedFile = $this->findDownloadedFile($tempDir, 'video');

            return $downloadedFile && Storage::disk('public')->exists($downloadedFile) ? $downloadedFile : null;

        } catch (\Exception $e) {
            Log::error('YouTube video download failed', [
                'video_id' => $videoId,
                'error' => 'Video download failed',
            ]);

            throw $e;
        }
    }

    /**
     * Find the downloaded file in temp directory.
     */
    private function findDownloadedFile(string $tempDir, string $filename = 'audio'): ?string
    {
        $files = Storage::disk('public')->allFiles($tempDir);

        foreach ($files as $file) {
            if (pathinfo($file, PATHINFO_FILENAME) === $filename) {
                return $file;
            }
        }

        return null;
    }

    /**
     * Clean up temporary directory.
     */
    public function cleanupTempDirectory(string $tempDir): void
    {
        if (Storage::disk('public')->exists($tempDir)) {
            Storage::disk('public')->deleteDirectory($tempDir);
        }
    }
}
