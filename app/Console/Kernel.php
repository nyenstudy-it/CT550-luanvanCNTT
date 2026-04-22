<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     */
    protected function schedule(Schedule $schedule): void
    {
        // Auto-close unclosed attendances daily at 19:00
        // This ensures any forgotten checkouts are closed at expected_end time
        $schedule->command('attendance:auto-close')
            ->dailyAt('19:00')
            ->onOneServer()
            ->appendOutputTo(storage_path('logs/auto-close-attendances.log'));

        // You can add more scheduled commands here
        // $schedule->command('inspire')->hourly();
    }

    /**
     * Register the commands for the application.
     */
    protected function commands(): void
    {
        $this->load(__DIR__ . '/Commands');

        require base_path('routes/console.php');
    }
}
