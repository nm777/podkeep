<?php

use PHPUnit\Framework\Assert;

test('production image and YouTube downloads enable a supported JavaScript runtime', function () {
    $repositoryRoot = getenv('PODKEEP_REPOSITORY_ROOT') ?: dirname(__DIR__, 3);
    $dockerfile = file_get_contents($repositoryRoot.'/Dockerfile');
    $downloader = file_get_contents($repositoryRoot.'/src/app/Services/YouTube/YouTubeDownloader.php');

    $baseStage = strstr($dockerfile, 'FROM base AS dev', true);

    Assert::assertIsString($baseStage);
    Assert::assertStringContainsString('    nodejs', $baseStage);
    Assert::assertSame(2, substr_count($downloader, "'--js-runtimes'"));
    Assert::assertSame(2, substr_count($downloader, "'node'"));
});
