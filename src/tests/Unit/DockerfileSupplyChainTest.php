<?php

use PHPUnit\Framework\Assert;

test('Dockerfile pins Composer, Whisper, and the Whisper model', function () {
    $dockerfile = file_get_contents((getenv('REPOSITORY_ROOT') ?: base_path('..')).'/Dockerfile');

    Assert::assertStringContainsString('composer:2@sha256:5946476338742b200bb9ff88f8be56275ddae4b3949c72305cb0dbf10cfcb760', $dockerfile);
    Assert::assertStringContainsString('WHISPER_COMMIT=8a9ad7844d6e2a10cddf4b92de4089d7ac2b14a9', $dockerfile);
    Assert::assertStringContainsString('WHISPER_MODEL_REVISION=5359861c739e955e79d9a303bcbc70fb988958b1', $dockerfile);
    Assert::assertStringContainsString('WHISPER_MODEL_SHA256=c6138d6d58ecc8322097e0f987c32f1be8bb0a18532a3f88f734d1bbf9c41e5d', $dockerfile);
    Assert::assertStringContainsString('sha256sum -c -', $dockerfile);
});
