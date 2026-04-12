<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;

uses(RefreshDatabase::class);

describe('Frontend route names exist', function () {
    $frontendRoutes = [
        'library.store',
        'login',
        'logout',
        'register',
        'password.request',
        'password.store',
        'password.update',
        'password.confirm',
        'password.email',
        'dashboard',
        'home',
        'profile.edit',
        'profile.update',
        'profile.destroy',
        'verification.send',
    ];

    foreach ($frontendRoutes as $routeName) {
        it("has route named '{$routeName}'", function () use ($routeName) {
            expect(Route::has($routeName))->toBeTrue("Route '{$routeName}' is missing. The frontend will crash with a Ziggy error.");
        });
    }
});
