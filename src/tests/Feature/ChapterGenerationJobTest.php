<?php

use App\Jobs\SegmentTranscriptIntoChapters;
use App\Jobs\TranscribeMediaFile;
use App\Models\Chapter;
use App\Models\MediaFile;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Process;

it('transcribes the media file and stores the transcript', function () {
    Process::fake([
        '*' => Process::result(output: json_encode([
            'transcription' => [
                ['offsets' => ['from' => 0, 'to' => 5000], 'text' => 'Welcome to the service.'],
                ['offsets' => ['from' => 5000, 'to' => 10000], 'text' => 'Let us pray.'],
            ],
        ])),
    ]);

    $mediaFile = MediaFile::factory()->create(['duration' => 600, 'mime_type' => 'audio/mpeg']);

    dispatch_sync(new TranscribeMediaFile($mediaFile));

    $transcript = $mediaFile->fresh()->transcript;
    expect($transcript)->not->toBeNull()->toHaveCount(2);
    expect($transcript[0])->toHaveKey('text', 'Welcome to the service.');
});

it('skips transcription when a transcript already exists', function () {
    // No Process::fake here: if the skip path works, transcribe() is never called
    // (and if it were, the real whisper.cpp call would error, failing the test).
    $existing = [['start' => 0, 'end' => 5, 'text' => 'cached']];
    $mediaFile = MediaFile::factory()->create(['duration' => 600, 'transcript' => $existing]);

    dispatch_sync(new TranscribeMediaFile($mediaFile));

    expect($mediaFile->fresh()->transcript)->toBe($existing);
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

it('extracts audio via ffmpeg before transcribing video media', function () {
    Process::fake([
        '*' => Process::result(output: json_encode([
            'transcription' => [['offsets' => ['from' => 0, 'to' => 5000], 'text' => 'Welcome.']],
        ])),
    ]);

    $mediaFile = MediaFile::factory()->create(['duration' => 600, 'mime_type' => 'video/mp4']);

    dispatch_sync(new TranscribeMediaFile($mediaFile));

    expect($mediaFile->fresh()->transcript)->toHaveCount(1);

    Process::assertRan(function ($process): bool {
        $command = is_array($process->command) ? implode(' ', $process->command) : (string) $process->command;

        return str_contains($command, 'ffmpeg');
    });
    Process::assertRan(function ($process): bool {
        $command = is_array($process->command) ? implode(' ', $process->command) : (string) $process->command;

        return str_contains($command, 'whisper');
    });
});
