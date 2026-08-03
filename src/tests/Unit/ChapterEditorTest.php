<?php

use PHPUnit\Framework\Assert;

test('chapter editor converts H:MM:SS markers to seconds before saving', function () {
    $editor = file_get_contents(dirname(__DIR__, 2).'/resources/js/components/chapter-editor.tsx');

    Assert::assertStringContainsString('function parseHms(value: string): number | null', $editor);
    Assert::assertStringContainsString('/^(\\d+):([0-5]\\d):([0-5]\\d)$/', $editor);
    Assert::assertStringContainsString('start_time: start_time ?? 0', $editor);
});
