<?php

namespace App\Services\Transcription;

use App\Models\MediaFile;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Process;

class WhisperClient
{
    /**
     * Split a source A/V file into 16 kHz mono WAV chunks of $chunkSeconds each,
     * ready to feed to whisper-cli.
     *
     * @return array{dir: string, segments: array<int, array{path: string, offset: int}>}
     */
    public function chunk(string $source, int $chunkSeconds): array
    {
        $dir = sys_get_temp_dir().'/whisper-chunks-'.uniqid('', true);
        File::ensureDirectoryExists($dir);

        $result = Process::timeout(300)->run([
            'ffmpeg', '-y', '-i', $source,
            '-vn', '-ac', '1', '-ar', '16000',
            '-f', 'segment', '-segment_time', (string) $chunkSeconds,
            $dir.'/chunk_%03d.wav',
        ]);

        if (! $result->successful()) {
            File::deleteDirectory($dir);
            throw new \RuntimeException('ffmpeg chunking failed: '.$result->errorOutput());
        }

        $paths = glob($dir.'/chunk_*.wav') ?: [];
        sort($paths);

        return [
            'dir' => $dir,
            'segments' => collect($paths)->map(fn ($path, $i) => [
                'path' => $path,
                'offset' => $i * $chunkSeconds,
            ])->all(),
        ];
    }

    /**
     * Transcribe a single WAV chunk, returning segments with times relative to the chunk start.
     *
     * @return array<int, array{start: int, end: int, text: string}>
     */
    public function transcribeFile(string $wavPath): array
    {
        // Per-chunk transcription is bounded; keep well under the queue job timeout.
        $result = Process::timeout(6600)->run([
            config('services.whisper.binary'),
            '-m', config('services.whisper.model_path'),
            '-f', $wavPath,
            '-np',
        ]);

        if (! $result->successful()) {
            throw new \RuntimeException('whisper.cpp failed: '.$result->errorOutput());
        }

        return $this->parse($result->output());
    }

    public function cleanupChunks(string $dir): void
    {
        File::deleteDirectory($dir);
    }

    /**
     * Parse whisper.cpp stdout segment format: `[H:MM:SS.mmm --> H:MM:SS.mmm]   text`.
     *
     * @return array<int, array{start: int, end: int, text: string}>
     */
    public function parse(string $output): array
    {
        preg_match_all(
            '/^\[(\d+):(\d+):(\d+(?:\.\d+)?)\s*-->\s*(\d+):(\d+):(\d+(?:\.\d+)?)\]\s*(.+)$/m',
            $output,
            $matches,
            PREG_SET_ORDER
        );

        return collect($matches)
            ->map(fn ($m) => [
                'start' => (int) round((int) $m[1] * 3600 + (int) $m[2] * 60 + (float) $m[3]),
                'end' => (int) round((int) $m[4] * 3600 + (int) $m[5] * 60 + (float) $m[6]),
                'text' => trim($m[7]),
            ])
            ->filter(fn ($seg) => $seg['text'] !== '')
            ->values()
            ->all();
    }
}
