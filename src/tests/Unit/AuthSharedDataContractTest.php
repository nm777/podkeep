<?php

use PHPUnit\Framework\Assert;

test('shared auth user is restricted to frontend fields and permits guests', function () {
    $types = file_get_contents(dirname(__DIR__, 2).'/resources/js/types/index.d.ts');
    $middleware = file_get_contents(dirname(__DIR__, 2).'/app/Http/Middleware/HandleInertiaRequests.php');

    Assert::assertStringContainsString('user: User | null;', $types);
    Assert::assertStringContainsString("'user' => \$request->user()?->only([", $middleware);
    Assert::assertStringNotContainsString("'user' => \$request->user(),", $middleware);
});
