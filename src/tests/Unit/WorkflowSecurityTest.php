<?php

use PHPUnit\Framework\Assert;

test('lint workflow is read-only and pins its actions', function () {
    $repositoryRoot = getenv('REPOSITORY_ROOT') ?: dirname(__DIR__, 3);
    $workflow = file_get_contents($repositoryRoot.'/.github/workflows/lint.yml');

    Assert::assertStringContainsString('contents: read', $workflow);
    Assert::assertStringNotContainsString('contents: write', $workflow);
    Assert::assertStringNotContainsString('git-auto-commit-action', $workflow);
    Assert::assertStringNotContainsString('git commit', $workflow);
    Assert::assertStringNotContainsString('vendor/bin/pint\n', $workflow);
    Assert::assertStringNotContainsString('npm run format\n', $workflow);
    Assert::assertStringNotContainsString('--write', $workflow);
    Assert::assertStringNotContainsString('--fix', $workflow);
    Assert::assertStringContainsString('npm ci', $workflow);
    Assert::assertStringContainsString('vendor/bin/pint --test', $workflow);
    Assert::assertStringContainsString('npm run format:check', $workflow);
    Assert::assertStringContainsString('npx eslint .', $workflow);
    Assert::assertStringContainsString('npm run types', $workflow);
    Assert::assertStringContainsString('vendor/bin/phpstan analyse --no-progress', $workflow);
    Assert::assertStringContainsString('FALLOW_CACHE_DIR: /tmp/fallow', $workflow);
    Assert::assertStringContainsString('npx fallow audit --base main', $workflow);
    Assert::assertDoesNotMatchRegularExpression('/uses:\s+\S+@(?![a-f0-9]{40}\b)/', $workflow);
});
