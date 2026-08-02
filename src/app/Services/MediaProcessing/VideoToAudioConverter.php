<?php

namespace App\Services\MediaProcessing;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;

class VideoToAudioConverter
{
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
