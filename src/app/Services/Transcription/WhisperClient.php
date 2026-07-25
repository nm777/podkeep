<?php

namespace App\Services\Transcription;

use App\Models\MediaFile;
use Illuminate\Support\Facades\Process;
use Illuminate\Support\Facades\Storage;

class WhisperClient
{
    /**
     * Transcribe a media file, returning timestamped segments.
     *
     * @return array<int, array{start: int, end: int, text: string}>
     */
    public function transcribe(MediaFile $mediaFile): array
    {
        $source = Storage::disk('public')->path($mediaFile->file_path);
        $audio = str_starts_with((string) $mediaFile->mime_type, 'video/') ? $this->extractAudio($source) : $source;

        $result = Process::run([
            config('services.whisper.binary'),
            '-m', config('services.whisper.model_path'),
            '-f', $audio,
            '-oj',
            '--no-print-progress',
        ]);

        if (! $result->successful()) {
            throw new \RuntimeException('whisper.cpp failed: '.$result->errorOutput());
        }

        return $this->parse($result->output());
    }

    /**
     * Extract a 16 kHz mono WAV from a video file (whisper.cpp ingests audio only).
     */
    protected function extractAudio(string $source): string
    {
        $wav = sys_get_temp_dir().'/'.pathinfo($source, PATHINFO_FILENAME).'-'.uniqid().'.wav';

        $result = Process::run([
            'ffmpeg', '-y', '-i', $source,
            '-vn', '-ac', '1', '-ar', '16000', '-f', 'wav', $wav,
        ]);

        if (! $result->successful()) {
            throw new \RuntimeException('ffmpeg audio extraction failed: '.$result->errorOutput());
        }

        return $wav;
    }

    /**
     * Parse whisper.cpp `-oj` JSON output into segments.
     *
     * @return array<int, array{start: int, end: int, text: string}>
     */
    protected function parse(string $output): array
    {
        // whisper.cpp writes JSON to stdout; tolerate leading noise by finding the JSON object.
        if (preg_match('/\{.*\}/s', $output, $matches)) {
            $output = $matches[0];
        }

        $decoded = json_decode($output, true);
        if (! is_array($decoded) || ! isset($decoded['transcription'])) {
            return [];
        }

        /** @var array<int, array<string, mixed>> $transcription */
        $transcription = $decoded['transcription'];

        return collect($transcription)
            ->map(fn (array $seg) => [
                'start' => (int) floor(($seg['offsets']['from'] ?? 0) / 1000),
                'end' => (int) floor(($seg['offsets']['to'] ?? 0) / 1000),
                'text' => trim((string) ($seg['text'] ?? '')),
            ])
            ->filter(fn (array $seg) => $seg['text'] !== '')
            ->values()
            ->all();
    }
}
