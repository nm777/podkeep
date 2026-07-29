<?php

use PHPUnit\Framework\Assert;

test('non PHP-FPM container commands run as www-data after setup', function () {
    $repositoryRoot = getenv('PODKEEP_REPOSITORY_ROOT') ?: dirname(__DIR__, 3);
    $entrypoint = file_get_contents($repositoryRoot.'/docker-entrypoint.sh');
    $compose = file_get_contents($repositoryRoot.'/docker-compose.prod.yml');

    Assert::assertStringContainsString('if [ "$1" = "php-fpm" ]; then', $entrypoint);
    Assert::assertStringContainsString('exec su-exec www-data "$@"', $entrypoint);
    Assert::assertStringContainsString('command: php artisan schedule:work', $compose);
    Assert::assertStringNotContainsString('entrypoint: ["sh", "-c"]', $compose);
});

test('Laravel production services mount their persistent runtime files', function () {
    $repositoryRoot = getenv('PODKEEP_REPOSITORY_ROOT') ?: dirname(__DIR__, 3);
    $compose = file_get_contents($repositoryRoot.'/docker-compose.prod.yml');

    foreach (['app', 'worker', 'chapters', 'scheduler'] as $service) {
        Assert::assertMatchesRegularExpression(
            '/^  '.$service.":\n(?:(?:    |  ).*\n)*?      - podcast-storage:\/var\/www\/html\/storage\n      - \.\/\.env:\/var\/www\/html\/\.env:ro\n      - \.\/database\.sqlite:\/var\/www\/html\/database\/database\.sqlite/m",
            $compose,
        );
    }
});

test('development Compose runs a dedicated chapters worker', function () {
    $repositoryRoot = getenv('PODKEEP_REPOSITORY_ROOT') ?: dirname(__DIR__, 3);
    $compose = file_get_contents($repositoryRoot.'/docker-compose.yml');

    Assert::assertMatchesRegularExpression(
        '/^  chapters:\n(?:(?:    |  ).*\n)*?    command: php -d memory_limit=512M artisan queue:work chapters --queue=chapters --sleep=5 --tries=3 --timeout=43200$/m',
        $compose,
    );
});
