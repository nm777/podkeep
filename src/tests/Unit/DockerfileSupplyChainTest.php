<?php

use PHPUnit\Framework\Assert;

test('external Docker images are pinned by digest', function () {
    $repositoryRoot = getenv('REPOSITORY_ROOT') ?: dirname(__DIR__, 3);
    $dockerfile = file_get_contents($repositoryRoot.'/Dockerfile');
    $compose = file_get_contents($repositoryRoot.'/docker-compose.yml');

    Assert::assertStringContainsString('php:8.4-fpm-alpine@sha256:5992f8b7433fe7fa96dfbf67746c86d6c41bc91e686eac38fe531c72a02e40e4', $dockerfile);
    Assert::assertStringContainsString('composer:2@sha256:5946476338742b200bb9ff88f8be56275ddae4b3949c72305cb0dbf10cfcb760', $dockerfile);
    Assert::assertStringContainsString('node:24-alpine@sha256:a0b9bf06e4e6193cf7a0f58816cc935ff8c2a908f81e6f1a95432d679c54fbfd', $dockerfile);
    Assert::assertStringContainsString('alpine:3.20@sha256:d9e853e87e55526f6b2917df91a2115c36dd7c696a35be12163d44e6e2a4b6bc', $dockerfile);
    Assert::assertStringContainsString('nginx:1.31-alpine@sha256:4a73073bd557c65b759505da037898b61f1be6cbcc3c2c3aeac22d2a470c1752', $dockerfile);
    Assert::assertStringContainsString('nginx:1.31-alpine@sha256:4a73073bd557c65b759505da037898b61f1be6cbcc3c2c3aeac22d2a470c1752', $compose);
});
