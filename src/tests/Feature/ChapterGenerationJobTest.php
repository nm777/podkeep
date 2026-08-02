<?php

use App\Jobs\SegmentTranscriptIntoChapters;
use App\Jobs\TranscribeMediaFile;
use App\Models\Chapter;
use App\Models\MediaFile;
use App\Services\LlmClient;
use App\Services\Transcription\WhisperClient;
use Illuminate\Support\Facades\Http;

it('hides transcript generation data from media file serialization', function () {
    $mediaFile = MediaFile::factory()->create([
        'transcript' => [['start' => 0, 'end' => 5, 'text' => 'Private transcript.']],
        'chapter_proposal' => [['start_time' => 0, 'title' => 'Private proposal']],
        'chapter_generation_error' => 'Private error',
    ]);

    $serialized = $mediaFile->toArray();

    $this->assertArrayNotHasKey('transcript', $serialized);
    $this->assertArrayNotHasKey('chapter_proposal', $serialized);
    $this->assertArrayNotHasKey('chapter_generation_error', $serialized);
});

it('transcribes chunked audio and offsets each chunk by its start time', function () {
    $this->app->instance(WhisperClient::class, new class extends WhisperClient
    {
        public function chunk(string $source, int $chunkSeconds): array
        {
            return ['dir' => '/tmp/fake', 'segments' => [
                ['path' => '/tmp/c0.wav', 'offset' => 0],
                ['path' => '/tmp/c1.wav', 'offset' => $chunkSeconds],
            ]];
        }

        public function transcribeFile(string $wavPath): array
        {
            return [['start' => 0, 'end' => 5, 'text' => 'Hello.'], ['start' => 5, 'end' => 10, 'text' => 'World.']];
        }

        public function cleanupChunks(string $dir): void {}
    });

    $mediaFile = MediaFile::factory()->create(['duration' => 3600, 'mime_type' => 'audio/mpeg']);

    dispatch_sync(new TranscribeMediaFile($mediaFile));

    $transcript = $mediaFile->fresh()->transcript;
    expect($transcript)->toHaveCount(4);
    expect($transcript[0])->toMatchArray(['start' => 0, 'text' => 'Hello.']);
    expect($transcript[2])->toMatchArray(['start' => 1800, 'text' => 'Hello.']); // offset by second chunk
});

it('resumes transcription from the saved checkpoint', function () {
    $fake = new class extends WhisperClient
    {
        /** @var array<int, string> */
        public array $transcribed = [];

        public function chunk(string $source, int $chunkSeconds): array
        {
            return ['dir' => '/tmp/fake', 'segments' => [
                ['path' => '/tmp/c0.wav', 'offset' => 0],
                ['path' => '/tmp/c1.wav', 'offset' => $chunkSeconds],
            ]];
        }

        public function transcribeFile(string $wavPath): array
        {
            $this->transcribed[] = $wavPath;

            return [['start' => 0, 'end' => 5, 'text' => 'more.']];
        }

        public function cleanupChunks(string $dir): void {}
    };
    $this->app->instance(WhisperClient::class, $fake);

    // Chunk 0 ([0,1800)) already transcribed; only chunk 1 should run.
    $mediaFile = MediaFile::factory()->create([
        'duration' => 3600,
        'transcript' => [['start' => 0, 'end' => 1800, 'text' => 'chunk zero done']],
    ]);

    dispatch_sync(new TranscribeMediaFile($mediaFile));

    expect($fake->transcribed)->toBe(['/tmp/c1.wav']);
    expect($mediaFile->fresh()->transcript)->toHaveCount(2);
});

it('skips transcription when the transcript already covers the file', function () {
    $this->app->instance(WhisperClient::class, new class extends WhisperClient
    {
        public function chunk(string $source, int $chunkSeconds): array
        {
            throw new RuntimeException('chunk() should not be called');
        }
    });

    $existing = [['start' => 0, 'end' => 600, 'text' => 'complete']];
    $mediaFile = MediaFile::factory()->create(['duration' => 600, 'transcript' => $existing]);

    dispatch_sync(new TranscribeMediaFile($mediaFile)); // no exception => chunk() never called

    expect($mediaFile->fresh()->transcript)->toBe($existing);
});

it('keeps transcription checkpoints for retries and clears them on terminal failure', function () {
    $fake = new class extends WhisperClient
    {
        public function chunk(string $source, int $chunkSeconds): array
        {
            return ['dir' => '/tmp/fake', 'segments' => [
                ['path' => '/tmp/c0.wav', 'offset' => 0],
                ['path' => '/tmp/c1.wav', 'offset' => $chunkSeconds],
            ]];
        }

        public function transcribeFile(string $wavPath): array
        {
            if ($wavPath === '/tmp/c1.wav') {
                throw new RuntimeException('whisper.cpp failed: model not found');
            }

            return [['start' => 0, 'end' => 5, 'text' => 'checkpoint']];
        }

        public function cleanupChunks(string $dir): void {}
    };

    $mediaFile = MediaFile::factory()->create(['duration' => 3600, 'mime_type' => 'audio/mpeg']);
    $job = new TranscribeMediaFile($mediaFile);

    expect(fn () => $job->handle($fake))->toThrow(RuntimeException::class, 'whisper.cpp failed: model not found');

    $checkpoint = $mediaFile->fresh();
    expect($checkpoint->transcript)->toBe([['start' => 0, 'end' => 5, 'text' => 'checkpoint']]);
    expect($checkpoint->chapter_generation_status)->toBe('failed');
    expect($checkpoint->chapter_generation_error)->toContain('whisper.cpp');

    $job->failed(new RuntimeException('whisper.cpp failed: model not found'));

    $failed = $mediaFile->fresh();
    expect($failed->transcript)->toBeNull();
    expect($failed->chapter_generation_status)->toBe('failed');
    expect($failed->chapter_generation_error)->toContain('whisper.cpp');
});

it('skips re-segmentation when chapters already exist for the current transcript', function () {
    Http::fake();

    $transcript = [['start' => 0, 'end' => 5, 'text' => 'Hi.']];
    $mediaFile = MediaFile::factory()->create([
        'duration' => 600,
        'transcript' => $transcript,
        'chapter_proposal_for_hash' => md5(json_encode($transcript)),
    ]);
    Chapter::factory()->create(['media_file_id' => $mediaFile->id, 'start_time' => 0, 'title' => 'Existing']);

    dispatch_sync(new SegmentTranscriptIntoChapters($mediaFile));

    Http::assertNothingSent(); // LLM was not called
    expect($mediaFile->fresh()->chapter_generation_status)->toBe('completed');
});

it('writes generated chapters directly to the chapters table (not a proposal)', function () {
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
    expect($fresh->transcript)->toBeNull();
    expect($fresh->chapter_proposal)->toBeNull();
    expect($fresh->chapter_proposal_for_hash)->toBe(md5(json_encode([
        ['start' => 0, 'end' => 5, 'text' => 'Hello world.'],
    ])));
    $chapters = Chapter::where('media_file_id', $mediaFile->id)->get();
    expect($chapters)->toHaveCount(2);
    expect($chapters[0])->toMatchArray(['start_time' => 0, 'title' => 'Intro']);

    dispatch_sync(new SegmentTranscriptIntoChapters($mediaFile));

    Http::assertSentCount(1);
});

it('marks status failed and rethrows when the LLM call fails', function () {
    Http::fake(['*/chat/completions' => Http::response([], 500)]);
    $mediaFile = MediaFile::factory()->create([
        'duration' => 600,
        'transcript' => [['start' => 0, 'end' => 5, 'text' => 'Hi.']],
    ]);

    $thrown = null;
    try {
        dispatch_sync(new SegmentTranscriptIntoChapters($mediaFile));
    } catch (Throwable $e) {
        $thrown = $e;
    }

    $fresh = $mediaFile->fresh();
    expect($thrown)->not->toBeNull();
    expect($fresh->chapter_generation_status)->toBe('failed');
    expect($fresh->chapter_generation_error)->not->toBeNull();
});

it('checkpoints completed LLM sections before retrying a failed section', function () {
    config(['services.llm.section_chars' => 5]);

    $mediaFile = MediaFile::factory()->create([
        'duration' => 600,
        'transcript' => [
            ['start' => 0, 'end' => 5, 'text' => 'first segment'],
            ['start' => 100, 'end' => 105, 'text' => 'second segment'],
        ],
    ]);
    $job = new SegmentTranscriptIntoChapters($mediaFile);

    Http::fake([
        '*/chat/completions' => Http::sequence()
            ->push(['choices' => [['message' => ['content' => json_encode(['chapters' => [['start' => 0, 'title' => 'Opening']]])]]]])
            ->push([], 500)
            ->push(['choices' => [['message' => ['content' => json_encode(['chapters' => [['start' => 100, 'title' => 'Chapter One']]])]]]]),
    ]);

    expect(fn () => $job->handle(new LlmClient))->toThrow(RuntimeException::class);
    expect($mediaFile->fresh()->chapter_proposal)->toBe([[['start' => 0, 'title' => 'Opening']]]);
    expect($job->backoff)->toBe([60, 300]);

    $job->handle(new LlmClient);

    Http::assertSentCount(3);
    expect($mediaFile->fresh()->chapters->pluck('title')->all())->toBe(['Opening', 'Chapter One']);
});

it('splits long transcripts into multiple LLM calls and merges the chapters', function () {
    // Tiny budget forces one section per segment -> multiple LLM calls (map-reduce).
    config(['services.llm.section_chars' => 5]);

    Http::fake([
        '*/chat/completions' => Http::response([
            'choices' => [['message' => ['content' => json_encode(['chapters' => [
                ['start' => 0, 'title' => 'A topic'],
            ]])]]],
        ]),
    ]);

    $transcript = [
        ['start' => 0, 'end' => 5, 'text' => 'first segment here'],
        ['start' => 5, 'end' => 10, 'text' => 'second segment here'],
        ['start' => 10, 'end' => 15, 'text' => 'third segment here'],
    ];

    $chapters = (new LlmClient)->proposeChapters($transcript, 600);

    // Three sections -> three calls; every call returned start 0, so dedup collapses to one.
    Http::assertSentCount(3);
    expect($chapters)->toHaveCount(1);
    expect($chapters[0])->toMatchArray(['start' => 0, 'title' => 'A topic']);
});

it('keeps distinct chapters across merged sections and dedupes boundary duplicates', function () {
    // Two sections; each returns a chapter at a distinct time -> both kept.
    config(['services.llm.section_chars' => 100]);

    Http::fakeSequence('*/chat/completions')
        ->push(['choices' => [['message' => ['content' => json_encode(['chapters' => [
            ['start' => 0, 'title' => 'Opening'],
        ]])]]]])
        ->push(['choices' => [['message' => ['content' => json_encode(['chapters' => [
            ['start' => 200, 'title' => 'Main point'],
        ]])]]]]);

    $transcript = [
        ['start' => 0, 'end' => 60, 'text' => str_repeat('a', 80)],
        ['start' => 200, 'end' => 260, 'text' => str_repeat('b', 80)],
    ];

    $chapters = (new LlmClient)->proposeChapters($transcript, 600);

    expect($chapters)->toHaveCount(2);
    expect($chapters[0])->toMatchArray(['start' => 0, 'title' => 'Opening']);
    expect($chapters[1])->toMatchArray(['start' => 200, 'title' => 'Main point']);
});

it('parses whisper stdout segments into timestamped text', function () {
    $client = new WhisperClient;

    $output = "[00:00:00.000 --> 00:00:17.000]   They were hopeless, beaten and so weary\n".
        "[00:00:17.000 --> 00:00:26.000]   Just to see the glorious sunrise\n";

    $segments = $client->parse($output);

    expect($segments)->toHaveCount(2);
    expect($segments[0])->toMatchArray(['start' => 0, 'end' => 17, 'text' => 'They were hopeless, beaten and so weary']);
    expect($segments[1])->toMatchArray(['start' => 17, 'end' => 26]);
});
