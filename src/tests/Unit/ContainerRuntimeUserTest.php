<?php

use PHPUnit\Framework\Assert;

test('non PHP-FPM container commands run as www-data after setup', function () {
    $repositoryRoot = getenv('REPOSITORY_ROOT') ?: base_path('..');
    $entrypoint = file_get_contents($repositoryRoot.'/docker-entrypoint.sh');
    $compose = file_get_contents($repositoryRoot.'/docker-compose.prod.yml');

    Assert::assertStringContainsString('if [ "$1" = "php-fpm" ]; then', $entrypoint);
    Assert::assertStringContainsString('exec su-exec www-data "$@"', $entrypoint);
    Assert::assertStringContainsString('command: php artisan schedule:work', $compose);
    Assert::assertStringNotContainsString('entrypoint: ["sh", "-c"]', $compose);
});
