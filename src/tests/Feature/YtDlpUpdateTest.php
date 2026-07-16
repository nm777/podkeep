<?php

use App\Console\Commands\UpdateYtDlp;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Support\Carbon;

it('builds a stable per-day cron expression for yt-dlp:update', function () {
    $day = Carbon::parse('2026-07-16');
    $sameDayLater = Carbon::parse('2026-07-16 23:59:30');
    $nextDay = Carbon::parse('2026-07-17');

    $expr = UpdateYtDlp::dailyCronExpression($day);

    // Stable within the same calendar day.
    expect(UpdateYtDlp::dailyCronExpression($sameDayLater))->toBe($expr);

    // Valid 5-part, daily-shaped cron (last three fields wildcarded).
    expect($expr)->toMatch('/^\d{1,2} \d{1,2} \* \* \*$/');

    // Hour 0-23, minute 0-59.
    [$minute, $hour] = explode(' ', $expr);
    expect((int) $hour)->toBeGreaterThanOrEqual(0)->toBeLessThanOrEqual(23);
    expect((int) $minute)->toBeGreaterThanOrEqual(0)->toBeLessThanOrEqual(59);

    // Time varies across days.
    expect(UpdateYtDlp::dailyCronExpression($nextDay))->not->toBe($expr);
});

it('registers yt-dlp:update in the schedule as a once-daily task', function () {
    // Boots the console kernel so the schedule callback is evaluated.
    $this->artisan('schedule:list')->assertSuccessful();

    $event = collect(app(Schedule::class)->events())
        ->first(fn ($e) => str_contains($e->command, 'yt-dlp:update'));

    expect($event)->not->toBeNull()
        ->and($event->expression)->toMatch('/^\d{1,2} \d{1,2} \* \* \*$/');
});
