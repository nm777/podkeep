<?php

use PHPUnit\Framework\Assert;

test('production image has the current YouTube download runtime', function () {
    $repositoryRoot = getenv('PODKEEP_REPOSITORY_ROOT') ?: dirname(__DIR__, 3);
    $dockerfile = file_get_contents($repositoryRoot.'/Dockerfile');
    $downloader = file_get_contents($repositoryRoot.'/src/app/Services/YouTube/YouTubeDownloader.php');

    $baseStage = strstr($dockerfile, 'FROM base AS dev', true);

    Assert::assertIsString($baseStage);
    Assert::assertStringContainsString('    nodejs', $baseStage);
    Assert::assertStringContainsString('/2026.08.19/yt-dlp_musllinux', $dockerfile);
    Assert::assertStringContainsString('f3dec9cfeaf304cec98290fe41c6ad465d4b747d302473559643e7af24929722', $dockerfile);
    Assert::assertSame(2, substr_count($downloader, "'--js-runtimes'"));
    Assert::assertSame(2, substr_count($downloader, "'node'"));
});
