<?php

use PHPUnit\Framework\Assert;

test('shared auth user type permits the middleware guest response', function () {
    $types = file_get_contents(dirname(__DIR__, 2).'/resources/js/types/index.d.ts');
    $middleware = file_get_contents(dirname(__DIR__, 2).'/app/Http/Middleware/HandleInertiaRequests.php');

    Assert::assertStringContainsString('user: User | null;', $types);
    Assert::assertStringContainsString("'user' => \$request->user(),", $middleware);
});
