<?php

use App\Models\MediaFile;
use App\Models\User;
use App\Services\MediaProcessing\VideoToAudioConverter;
use Illuminate\Support\Facades\Storage;

test('updates metadata for indexed MP3 files', function () {
    Storage::fake('media');
    $content = 'indexed audio';
    $filePath = 'media/audio.mp3';
    Storage::disk('media')->put($filePath, $content);

    $mediaFile = MediaFile::factory()->create([
        'user_id' => User::factory(),
        'file_path' => $filePath,
        'mime_type' => 'audio/mpeg',
        'file_hash' => hash('sha256', 'unindexed audio'),
        'filesize' => 0,
    ]);

    app()->instance(VideoToAudioConverter::class, new class extends VideoToAudioConverter
    {
        public function ensureMp3SeekIndex(string $audioPath): bool
        {
            return true;
        }
    });

    $this->artisan('media:add-mp3-seek-indexes')->assertSuccessful();

    $mediaFile->refresh();

    expect($mediaFile->file_hash)->toBe(hash('sha256', $content))
        ->and($mediaFile->filesize)->toBe(strlen($content));
});
