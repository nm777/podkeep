<?php

use Illuminate\Console\Scheduling\Schedule;

it('does not schedule yt-dlp self-updates', function () {
    // Boots the console kernel so the schedule callback is evaluated.
    $this->artisan('schedule:list')->assertSuccessful();

    expect(collect(app(Schedule::class)->events())
        ->contains(fn ($event) => str_contains($event->command, 'yt-dlp:update')))
        ->toBeFalse();
});
