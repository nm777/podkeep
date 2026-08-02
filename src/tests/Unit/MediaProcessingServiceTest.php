<?php

use App\Models\LibraryItem;
use App\Models\User;
use App\Services\MediaProcessing\MediaDownloader;
use App\Services\MediaProcessing\MediaProcessingService;
use App\Services\MediaProcessing\MediaStorageManager;
use App\Services\MediaProcessing\MediaValidator;
use App\Services\MediaProcessing\UnifiedDuplicateProcessor;
use App\Services\MediaProcessing\VideoToAudioConverter;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

beforeEach(function () {
    Storage::fake('media');
});

test('cleans converted and source temporary files without deleting moved media', function () {
    $sourcePath = 'temp-downloads/video.mp4';
    $convertedPath = 'temp-downloads/video.mp3';
    $content = 'converted audio';
    $hash = hash('sha256', $content);
    $finalPath = 'media/'.$hash.'.mp3';

    Storage::disk('media')->put($sourcePath, hex2bin('000000186674797069736f6d0000020069736f6d69736f32617663316d703431'));
    Storage::disk('media')->put($convertedPath, $content);

    $service = mediaProcessingService($sourcePath, $convertedPath, $hash, $content);
    $libraryItem = LibraryItem::factory()->create(['user_id' => User::factory()]);

    $result = $service->processFromUrl($libraryItem, 'https://example.com/video.mp4', 'audio');

    expect($result['media_file'])->not->toBeNull();
    Storage::disk('media')->assertMissing([$sourcePath, $convertedPath]);
    Storage::disk('media')->assertExists($finalPath);
});

test('cleans converted and source temporary files when processing fails', function () {
    $sourcePath = 'temp-downloads/video.mp4';
    $convertedPath = 'temp-downloads/video.mp3';

    Storage::disk('media')->put($sourcePath, hex2bin('000000186674797069736f6d0000020069736f6d69736f32617663316d703431'));
    Storage::disk('media')->put($convertedPath, 'converted audio');

    $service = mediaProcessingService($sourcePath, $convertedPath, null, null, true);
    $libraryItem = LibraryItem::factory()->create(['user_id' => User::factory()]);

    $result = $service->processFromUrl($libraryItem, 'https://example.com/video.mp4', 'audio');

    expect($result['error'])->toBe('media_processing_failed');
    Storage::disk('media')->assertMissing([$sourcePath, $convertedPath]);
});

test('does not persist or return exception text', function () {
    $sourcePath = 'temp-downloads/video.mp4';
    $convertedPath = 'temp-downloads/video.mp3';
    $sensitiveError = 'Invalid audio from https://user:secret@example.com/audio.mp3?token=secret';

    Storage::disk('media')->put($sourcePath, hex2bin('000000186674797069736f6d0000020069736f6d69736f32617663316d703431'));
    Storage::disk('media')->put($convertedPath, 'converted audio');

    $service = mediaProcessingService($sourcePath, $convertedPath, null, null, true, $sensitiveError);
    $libraryItem = LibraryItem::factory()->create(['user_id' => User::factory()]);

    $result = $service->processFromUrl($libraryItem, 'https://example.com/video.mp4', 'audio');

    $libraryItem->refresh();

    $this->assertSame('Media processing failed.', $libraryItem->processing_error);
    $this->assertStringNotContainsString($sensitiveError, (string) $libraryItem->processing_error);
    $this->assertSame([
        'is_duplicate' => false,
        'media_file' => null,
        'error' => 'media_processing_failed',
        'message' => 'Media processing failed.',
    ], $result);
});

function mediaProcessingService(
    string $sourcePath,
    string $convertedPath,
    ?string $hash,
    ?string $content,
    bool $validationFails = false,
    string $validationError = 'Invalid audio',
): MediaProcessingService {
    $downloader = new class($sourcePath) extends MediaDownloader
    {
        public function __construct(private string $sourcePath) {}

        public function downloadFromUrl(string $url): string
        {
            return $this->sourcePath;
        }
    };

    $duplicateProcessor = new class extends UnifiedDuplicateProcessor
    {
        /** @return array{media_file: null} */
        public function processUrlDuplicate(LibraryItem $libraryItem, string $sourceUrl): array
        {
            return ['media_file' => null];
        }

        /** @return array{media_file: null} */
        public function processFileDuplicate(LibraryItem $libraryItem, string $filePath): array
        {
            return ['media_file' => null];
        }
    };

    $converter = new class($convertedPath) extends VideoToAudioConverter
    {
        public function __construct(private string $convertedPath) {}

        public function convert(string $videoPath): string
        {
            return $this->convertedPath;
        }
    };

    $storageManager = new class($convertedPath, $hash, $content) extends MediaStorageManager
    {
        public function __construct(
            private string $convertedPath,
            private ?string $hash,
            private ?string $content,
        ) {}

        public function fileExists(string $filePath): bool
        {
            return $filePath === $this->convertedPath;
        }

        /** @return array{file_path: string, file_hash: string, mime_type: string, filesize: int} */
        public function moveTempFile(string $tempPath, ?string $sourceUrl = null): array
        {
            $hash = $this->hash;
            $content = $this->content;

            if ($hash === null || $content === null) {
                throw new LogicException('The test did not configure a permanent file.');
            }

            $finalPath = 'media/'.$hash.'.mp3';
            Storage::disk('media')->move($tempPath, $finalPath);

            return [
                'file_path' => $finalPath,
                'file_hash' => $hash,
                'mime_type' => 'audio/mpeg',
                'filesize' => strlen($content),
            ];
        }
    };

    $validator = new class($validationFails, $validationError) extends MediaValidator
    {
        public function __construct(private bool $validationFails, private string $validationError) {}

        /** @return array{} */
        public function validate(string $filePath): array
        {
            if ($this->validationFails) {
                throw new InvalidArgumentException($this->validationError);
            }

            return [];
        }
    };

    return new MediaProcessingService(
        $downloader,
        $validator,
        $storageManager,
        $duplicateProcessor,
        $converter,
    );
}
