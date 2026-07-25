<?php

namespace App\Jobs;

use App\Models\MediaFile;
use App\Services\Transcription\WhisperClient;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class TranscribeMediaFile implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(public MediaFile $mediaFile)
    {
        $this->onQueue('chapters');
    }

    public function handle(WhisperClient $whisper): void
    {
        if ($this->mediaFile->fresh()->transcript) {
            return; // reuse cached transcript on re-proposal
        }

        $this->mediaFile->update(['chapter_generation_status' => 'processing']);

        try {
            $transcript = $whisper->transcribe($this->mediaFile);

            $this->mediaFile->update(['transcript' => $transcript]);
        } catch (\Throwable $e) {
            // Mark failed and rethrow so the chain stops (SegmentTranscript won't run with no transcript).
            $this->mediaFile->update([
                'chapter_generation_status' => 'failed',
                'chapter_generation_error' => $e->getMessage(),
            ]);

            throw $e;
        }
    }
}
