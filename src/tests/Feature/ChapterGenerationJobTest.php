<?php

use App\Jobs\SegmentTranscriptIntoChapters;
use App\Jobs\TranscribeMediaFile;
use App\Models\Chapter;
use App\Models\MediaFile;
use App\Services\Transcription\WhisperClient;
use Illuminate\Support\Facades\Http;

it('transcribes the media file and stores the transcript', function () {
    $this->app->instance(WhisperClient::class, new class extends WhisperClient
    {
        public function transcribe(MediaFile $mediaFile): array
        {
            return [
                ['start' => 0, 'end' => 5, 'text' => 'Welcome to the service.'],
                ['start' => 5, 'end' => 10, 'text' => 'Let us pray.'],
            ];
        }
    });

    $mediaFile = MediaFile::factory()->create(['duration' => 600, 'mime_type' => 'audio/mpeg']);

    dispatch_sync(new TranscribeMediaFile($mediaFile));

    $transcript = $mediaFile->fresh()->transcript;
    expect($transcript)->not->toBeNull()->toHaveCount(2);
    expect($transcript[0])->toHaveKey('text', 'Welcome to the service.');
});

it('skips transcription when a transcript already exists', function () {
    $existing = [['start' => 0, 'end' => 5, 'text' => 'cached']];
    $mediaFile = MediaFile::factory()->create(['duration' => 600, 'transcript' => $existing]);

    dispatch_sync(new TranscribeMediaFile($mediaFile));

    expect($mediaFile->fresh()->transcript)->toBe($existing);
});

it('marks status failed and rethrows when transcription fails', function () {
    $this->app->instance(WhisperClient::class, new class extends WhisperClient
    {
        public function transcribe(MediaFile $mediaFile): array
        {
            throw new \RuntimeException('whisper.cpp failed: model not found');
        }
    });

    $mediaFile = MediaFile::factory()->create(['duration' => 600, 'mime_type' => 'audio/mpeg']);

    $thrown = null;
    try {
        dispatch_sync(new TranscribeMediaFile($mediaFile));
    } catch (\Throwable $e) {
        $thrown = $e;
    }

    expect($thrown)->not->toBeNull();
    $fresh = $mediaFile->fresh();
    expect($fresh->chapter_generation_status)->toBe('failed');
    expect($fresh->chapter_generation_error)->toContain('whisper.cpp');
    expect($fresh->transcript)->toBeNull();
});

it('segments the transcript into a sanitized proposal and does not publish chapters', function () {
    Http::fake([
        '*/chat/completions' => Http::response([
            'choices' => [[
                'message' => ['content' => json_encode(['chapters' => [
                    ['start' => 0, 'title' => 'Intro'],
                    ['start' => 99999, 'title' => 'Out Of Range'],
                    ['start' => 100, 'title' => ''],
                    ['start' => 100, 'title' => 'Duplicate'],
                ]])],
            ]],
        ]),
    ]);

    $mediaFile = MediaFile::factory()->create([
        'duration' => 600,
        'transcript' => [['start' => 0, 'end' => 5, 'text' => 'Hello world.']],
    ]);

    dispatch_sync(new SegmentTranscriptIntoChapters($mediaFile));

    $fresh = $mediaFile->fresh();
    expect($fresh->chapter_generation_status)->toBe('completed');
    expect($fresh->chapter_proposal)->toHaveCount(2);
    expect($fresh->chapter_proposal[0])->toMatchArray(['start_time' => 0, 'title' => 'Intro']);
    expect(Chapter::where('media_file_id', $mediaFile->id)->count())->toBe(0);
});

it('marks status failed when the LLM call fails', function () {
    Http::fake(['*/chat/completions' => Http::response([], 500)]);
    $mediaFile = MediaFile::factory()->create([
        'duration' => 600,
        'transcript' => [['start' => 0, 'end' => 5, 'text' => 'Hi.']],
    ]);

    dispatch_sync(new SegmentTranscriptIntoChapters($mediaFile));

    $fresh = $mediaFile->fresh();
    expect($fresh->chapter_generation_status)->toBe('failed');
    expect($fresh->chapter_generation_error)->not->toBeNull();
});

it('parses whisper stdout segments into timestamped text', function () {
    $client = new WhisperClient();

    $output = "[00:00:00.000 --> 00:00:17.000]   They were hopeless, beaten and so weary\n".
        "[00:00:17.000 --> 00:00:26.000]   Just to see the glorious sunrise\n";

    $segments = $client->parse($output);

    expect($segments)->toHaveCount(2);
    expect($segments[0])->toMatchArray(['start' => 0, 'end' => 17, 'text' => 'They were hopeless, beaten and so weary']);
    expect($segments[1])->toMatchArray(['start' => 17, 'end' => 26]);
});
