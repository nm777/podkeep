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

        // whisper.cpp only reads WAV in this build, so always decode to 16 kHz mono WAV first.
        $wav = $this->toWav($source);

        try {
            $result = Process::run([
                config('services.whisper.binary'),
                '-m', config('services.whisper.model_path'),
                '-f', $wav,
                '-np',
            ]);
        } finally {
            @unlink($wav);
        }

        if (! $result->successful()) {
            throw new \RuntimeException('whisper.cpp failed: '.$result->errorOutput());
        }

        return $this->parse($result->output());
    }

    /**
     * Decode any A/V input to a 16 kHz mono WAV (the format whisper.cpp ingests).
     */
    protected function toWav(string $source): string
    {
        $wav = sys_get_temp_dir().'/whisper-'.uniqid('', true).'.wav';

        $result = Process::run([
            'ffmpeg', '-y', '-i', $source,
            '-vn', '-ac', '1', '-ar', '16000', '-f', 'wav', $wav,
        ]);

        if (! $result->successful() || ! file_exists($wav)) {
            throw new \RuntimeException('ffmpeg audio conversion failed: '.$result->errorOutput());
        }

        return $wav;
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
