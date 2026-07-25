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

        $transcript = $whisper->transcribe($this->mediaFile);

        $this->mediaFile->update(['transcript' => $transcript]);
    }
}
