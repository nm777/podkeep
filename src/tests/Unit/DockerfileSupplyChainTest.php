<?php

use PHPUnit\Framework\Assert;

test('external Docker images are pinned by digest', function () {
    $repositoryRoot = getenv('REPOSITORY_ROOT') ?: dirname(__DIR__, 3);
    $dockerfile = file_get_contents($repositoryRoot.'/Dockerfile');
    $compose = file_get_contents($repositoryRoot.'/docker-compose.yml');

    Assert::assertStringContainsString('php:8.4-fpm-alpine@sha256:c0e41e0c856bf97b2e749f40a804031ce938d0d496c4c7d91ad416d993172826', $dockerfile);
    Assert::assertStringContainsString('composer:2@sha256:5b2137f112f939a92788ceb5e55f54859d9dbf467a44037cf7760bdda949f365', $dockerfile);
    Assert::assertStringContainsString('node:24-alpine@sha256:116b800c3754b440ee0aa3ed2f20f5bf3d99a554bfd9adeea09ea8017f6e85f5', $dockerfile);
    Assert::assertStringContainsString('alpine:3.20@sha256:d9e853e87e55526f6b2917df91a2115c36dd7c696a35be12163d44e6e2a4b6bc', $dockerfile);
    Assert::assertStringContainsString('nginx:1.31-alpine@sha256:7fc0fb4af7bf006aeb31aea88069976060f8f0a0a86b667126742cc17f4e31ae', $dockerfile);
    Assert::assertStringContainsString('nginx:1.31-alpine@sha256:7fc0fb4af7bf006aeb31aea88069976060f8f0a0a86b667126742cc17f4e31ae', $compose);
});
