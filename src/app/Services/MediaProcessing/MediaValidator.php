<?php

namespace App\Services\MediaProcessing;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Process;

class MediaValidator
{
    /**
     * Validate media file and return metadata.
     * Only reads the file header for signature validation, not the entire file.
     */
    public function validate(string $filePath): array
    {
        $header = file_get_contents($filePath, false, null, 0, 4096);

        $this->validateMediaContent($header);

        return [
            'mime_type' => $this->detectMimeType($filePath, $header),
            'filesize' => file_exists($filePath) ? filesize($filePath) : 0,
            'duration' => $this->probeDuration($filePath),
            'is_valid' => true,
        ];
    }

    /**
     * Probe media duration in whole seconds via ffprobe. Returns null if unavailable.
     */
    public function probeDuration(string $filePath): ?int
    {
        $result = Process::run([
            'ffprobe', '-v', 'error',
            '-show_entries', 'format=duration',
            '-of', 'default=noprint_wrappers=1:nokey=1',
            $filePath,
        ]);

        if (! $result->successful()) {
            return null;
        }

        $duration = trim($result->output());

        return is_numeric($duration) ? (int) round((float) $duration) : null;
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
            // Video signatures
            "\x1A\x45\xDF\xA3" => true, // WebM/Matroska (EBML header)
            'ftyp' => true, // MP4/M4V (appears at offset 4)
        ];

        $fileSignature = substr($header, 0, 4);
        $isValidMedia = isset($validMediaSignatures[$fileSignature]) ||
                       isset($validMediaSignatures[substr($header, 0, 2)]) ||
                       str_starts_with($fileSignature, 'ID3') ||
                       substr($header, 4, 4) === 'ftyp';

        if (! $isValidMedia && strlen($header) > 100) {
            throw new \InvalidArgumentException('Content does not appear to be a valid media file');
        }
    }

    /**
     * Detect MIME type for media file.
     */
    private function detectMimeType(string $filePath, string $header): string
    {
        if (file_exists($filePath)) {
            $mimeType = File::mimeType($filePath);
            if ($mimeType && $mimeType !== 'text/plain') {
                return $mimeType;
            }
        }

        return $this->detectMimeTypeFromContent($header);
    }

    /**
     * Detect MIME type from content signature.
     */
    private function detectMimeTypeFromContent(string $header): string
    {
        $signatures = [
            'RIFF' => 'audio/wav',
            'OggS' => 'audio/ogg',
            'fLaC' => 'audio/flac',
            'MP4' => 'audio/mp4',
            "\xFF\xFB" => 'audio/mpeg',
            "\xFF\xF3" => 'audio/mpeg',
            "\xFF\xF2" => 'audio/mpeg',
            'ID3' => 'audio/mpeg',
            "\x1A\x45\xDF\xA3" => 'video/webm',
        ];

        foreach ($signatures as $signature => $mimeType) {
            if (str_starts_with($header, $signature)) {
                return $mimeType;
            }
        }

        return 'application/octet-stream';
    }
}
