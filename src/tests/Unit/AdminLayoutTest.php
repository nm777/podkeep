<?php

use PHPUnit\Framework\Assert;

test('admin layout derives its current URL from Inertia page data', function () {
    $layout = file_get_contents(dirname(__DIR__, 2).'/resources/js/layouts/admin-layout.tsx');

    Assert::assertStringContainsString("import { Link, usePage } from '@inertiajs/react';", $layout);
    Assert::assertStringContainsString('const { url } = usePage();', $layout);
    Assert::assertStringNotContainsString('window.location', $layout);
});
