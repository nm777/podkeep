<?php

use PHPUnit\Framework\Assert;

test('lint workflow is read-only and pins its actions', function () {
    $repositoryRoot = getenv('REPOSITORY_ROOT') ?: base_path('..');
    $workflow = file_get_contents($repositoryRoot.'/.github/workflows/lint.yml');

    Assert::assertStringContainsString('contents: read', $workflow);
    Assert::assertStringNotContainsString('contents: write', $workflow);
    Assert::assertStringNotContainsString('git-auto-commit-action', $workflow);
    Assert::assertStringNotContainsString('git commit', $workflow);
    Assert::assertDoesNotMatchRegularExpression('/uses:\s+\S+@(?![a-f0-9]{40}\b)/', $workflow);
});
