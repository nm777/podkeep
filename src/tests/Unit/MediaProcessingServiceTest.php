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
    Storage::fake('public');
});

test('cleans converted and source temporary files without deleting moved media', function () {
    $sourcePath = 'temp-downloads/video.mp4';
    $convertedPath = 'temp-downloads/video.mp3';
    $content = 'converted audio';
    $hash = hash('sha256', $content);
    $finalPath = 'media/'.$hash.'.mp3';

    Storage::disk('public')->put($sourcePath, hex2bin('000000186674797069736f6d0000020069736f6d69736f32617663316d703431'));
    Storage::disk('public')->put($convertedPath, $content);

    $service = mediaProcessingService($sourcePath, $convertedPath, $hash, $content);
    $libraryItem = LibraryItem::factory()->create(['user_id' => User::factory()]);

    $result = $service->processFromUrl($libraryItem, 'https://example.com/video.mp4', 'audio');

    expect($result['media_file'])->not->toBeNull();
    Storage::disk('public')->assertMissing([$sourcePath, $convertedPath]);
    Storage::disk('public')->assertExists($finalPath);
});

test('cleans converted and source temporary files when processing fails', function () {
    $sourcePath = 'temp-downloads/video.mp4';
    $convertedPath = 'temp-downloads/video.mp3';

    Storage::disk('public')->put($sourcePath, hex2bin('000000186674797069736f6d0000020069736f6d69736f32617663316d703431'));
    Storage::disk('public')->put($convertedPath, 'converted audio');

    $service = mediaProcessingService($sourcePath, $convertedPath, null, null, true);
    $libraryItem = LibraryItem::factory()->create(['user_id' => User::factory()]);

    $result = $service->processFromUrl($libraryItem, 'https://example.com/video.mp4', 'audio');

    expect($result['error'])->toBe('Invalid audio');
    Storage::disk('public')->assertMissing([$sourcePath, $convertedPath]);
});

function mediaProcessingService(
    string $sourcePath,
    string $convertedPath,
    ?string $hash,
    ?string $content,
    bool $validationFails = false,
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
            Storage::disk('public')->move($tempPath, $finalPath);

            return [
                'file_path' => $finalPath,
                'file_hash' => $hash,
                'mime_type' => 'audio/mpeg',
                'filesize' => strlen($content),
            ];
        }
    };

    $validator = new class($validationFails) extends MediaValidator
    {
        public function __construct(private bool $validationFails) {}

        /** @return array{} */
        public function validate(string $filePath): array
        {
            if ($this->validationFails) {
                throw new RuntimeException('Invalid audio');
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
