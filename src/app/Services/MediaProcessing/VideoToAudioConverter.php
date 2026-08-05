<?php

namespace App\Services\MediaProcessing;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;

class VideoToAudioConverter
{
    /**
     * Add a Xing seek index to variable-bitrate MP3 files without re-encoding.
     */
    public function ensureMp3SeekIndex(string $audioPath): bool
    {
        if (strtolower(pathinfo($audioPath, PATHINFO_EXTENSION)) !== 'mp3') {
            return false;
        }

        $absoluteInput = Storage::disk('media')->path($audioPath);

        if ($this->hasMp3SeekIndex($absoluteInput)) {
            return false;
        }

        $indexedPath = dirname($audioPath).'/'.pathinfo($audioPath, PATHINFO_FILENAME).'.indexed.mp3';
        $absoluteOutput = Storage::disk('media')->path($indexedPath);

        Storage::disk('media')->delete($indexedPath);

        $process = new Process([
            'ffmpeg', '-i', $absoluteInput,
            '-map', '0',
            '-c', 'copy',
            '-write_xing', '1',
            '-y',
            $absoluteOutput,
        ]);
        $process->setTimeout(600);
        $process->run();

        if (! $process->isSuccessful()) {
            Storage::disk('media')->delete($indexedPath);

            throw new ProcessFailedException($process);
        }

        if (! rename($absoluteOutput, $absoluteInput)) {
            Storage::disk('media')->delete($indexedPath);

            throw new \RuntimeException('Failed to replace MP3 with indexed copy.');
        }

        return true;
    }

    private function hasMp3SeekIndex(string $path): bool
    {
        $header = file_get_contents($path, false, null, 0, 10);
        $offset = 0;

        if ($header !== false && strlen($header) === 10 && str_starts_with($header, 'ID3')) {
            $offset = 10;

            for ($index = 6; $index < 10; $index++) {
                $offset += (ord($header[$index]) & 0x7F) << (7 * (9 - $index));
            }

            if (ord($header[5]) & 0x10) {
                $offset += 10;
            }
        }

        $firstFrame = file_get_contents($path, false, null, $offset, 512);

        return $firstFrame !== false && (str_contains($firstFrame, 'Xing') || str_contains($firstFrame, 'Info'));
    }

    /**
     * Extract audio from a video file using ffmpeg.
     *
     * @param  string  $videoPath  Relative path on the public disk
     * @return string Relative path of the converted audio file
     */
    public function convert(string $videoPath): string
    {
        $absoluteInput = Storage::disk('media')->path($videoPath);
        $outputDir = dirname($videoPath);
        $outputName = pathinfo($videoPath, PATHINFO_FILENAME).'.mp3';
        $outputPath = $outputDir.'/'.$outputName;
        $absoluteOutput = Storage::disk('media')->path($outputPath);

        $command = [
            'ffmpeg',
            '-i', $absoluteInput,
            '-vn',
            '-acodec', 'libmp3lame',
            '-q:a', '0',
            '-y',
            $absoluteOutput,
        ];

        Log::info('Converting video to audio', [
            'input' => $videoPath,
            'output' => $outputPath,
        ]);

        $process = new Process($command);
        $process->setTimeout(600); // 10 minutes
        $process->run();

        if (! $process->isSuccessful()) {
            Log::error('Video to audio conversion failed', [
                'exit_code' => $process->getExitCode(),
                'error_output' => $process->getErrorOutput(),
            ]);
            throw new ProcessFailedException($process);
        }

        Log::info('Video to audio conversion completed', [
            'output' => $outputPath,
            'size' => Storage::disk('media')->size($outputPath),
        ]);

        return $outputPath;
    }
}
