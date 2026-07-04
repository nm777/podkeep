<?php

use Illuminate\Support\Facades\Route;

Route::middleware(['throttle:api', 'auth:sanctum', 'eligible.api'])
    ->prefix('v1')
    ->group(function () {
        // API v1 routes will be added here
    });
