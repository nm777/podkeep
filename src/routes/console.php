<?php

use App\Console\Commands\RedownloadMedia;
use App\Console\Commands\ResetStuckProcessing;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

app()->make(RedownloadMedia::class);
app()->make(ResetStuckProcessing::class);
