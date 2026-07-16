<?php

namespace App\Console\Commands;

use DateTimeInterface;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Symfony\Component\Process\Process;

class UpdateYtDlp extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'yt-dlp:update';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check for and install yt-dlp updates via its native self-update';

    /**
     * Build a daily cron expression for a deterministic, per-day pseudo-random time.
     *
     * The expression is stable for a given calendar day (so schedule:run fires
     * exactly once that day) and changes each following day, with no stored
     * state required. schedule:run re-evaluates the schedule every minute, so
     * the date-seeded value is recomputed correctly as days roll over.
     *
     * @return string
     */
    public static function dailyCronExpression(DateTimeInterface $date): string
    {
        // ponytail: date-seeded crc32 yields a stable per-day pseudo-random time;
        // the tiny modulo bias is irrelevant for a once-daily admin task.
        $minutes = crc32($date->format('Y-m-d')) % 1440;

        return ($minutes % 60).' '.intdiv($minutes, 60).' * * *';
    }

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $binary = trim((string) exec('command -v yt-dlp 2>/dev/null'));

        if ($binary === '' || ! is_executable($binary)) {
            $this->error('yt-dlp binary not found or not executable on PATH.');

            Log::warning('yt-dlp:update: yt-dlp binary not found on PATH');

            return self::FAILURE;
        }

        $before = trim((string) exec('yt-dlp --version 2>/dev/null'));

        $process = new Process(['yt-dlp', '-U']);
        $process->setTimeout(120);
        $process->run();

        $after = trim((string) exec('yt-dlp --version 2>/dev/null'));

        if (! $process->isSuccessful()) {
            $this->error('yt-dlp self-update failed.');

            Log::error('yt-dlp self-update failed', [
                'version' => $before,
                'exit_code' => $process->getExitCode(),
                'output' => $process->getOutput(),
                'error_output' => $process->getErrorOutput(),
            ]);

            return self::FAILURE;
        }

        if ($before !== '' && $before !== $after) {
            $this->info("yt-dlp updated: {$before} -> {$after}");

            Log::info('yt-dlp updated', ['before' => $before, 'after' => $after]);
        } else {
            $this->info("yt-dlp is up to date ({$after})");

            Log::info('yt-dlp is up to date', ['version' => $after]);
        }

        return self::SUCCESS;
    }
}
