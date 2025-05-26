<?php

namespace App\Console;

use App\Console\Commands\DeleteOldExpertSchedules;
use App\Console\Commands\UpdateBookingStatus;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    protected $commands = [
        DeleteOldExpertSchedules::class,
        UpdateBookingStatus::class,
    ];

    protected function schedule(Schedule $schedule)
    {
        $schedule->command('app:delete-old-expert-schedules')->dailyAt('00:10');
        $schedule->command('app:update-booking-status')->everyMinute();
        $schedule->call(function () {
            \Log::info('[CRON TEST] schedule:run работает');
        })->everyMinute();
    }

    protected function commands()
    {
        $this->load(__DIR__ . '/Commands');
    }
}
