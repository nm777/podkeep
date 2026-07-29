<?php

namespace App\Services\YouTube;

use App\Services\YouTubeUrlValidator;
use Illuminate\Support\Facades\Log;
use Symfony\Component\Process\Process;

class YouTubeMetadataExtractor
{
    /**
     * Extract video metadata from YouTube URL.
     */
    public function extractMetadata(string $youtubeUrl): ?array
    {
        $videoId = YouTubeUrlValidator::extractVideoId($youtubeUrl);

        try {
            $metadataCommand = [
                'yt-dlp',
                '--dump-json',
                '--no-playlist',
                $youtubeUrl,
            ];

            $process = new Process($metadataCommand);
            $process->setTimeout(120);
            $process->run();
            $metadata = null;

            Log::info('Metadata command completed', [
                'video_id' => $videoId,
                'exit_code' => $process->getExitCode(),
            ]);

            if ($process->isSuccessful()) {
                $metadata = json_decode($process->getOutput(), true);
            } else {
                Log::error('Failed to extract metadata', [
                    'video_id' => $videoId,
                    'exit_code' => $process->getExitCode(),
                    'error' => 'Metadata command failed',
                ]);

                return null;
            }

            return [
                'title' => $metadata['title'] ?? null,
                'description' => $metadata['description'] ?? null,
                'upload_date' => $metadata['upload_date'] ?? null,
            ];

        } catch (\Exception $e) {
            Log::error('Metadata extraction failed', [
                'video_id' => $videoId,
                'error' => 'Metadata extraction failed',
            ]);

            return null;
        }
    }
}
