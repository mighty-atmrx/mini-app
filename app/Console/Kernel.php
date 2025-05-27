<?php

namespace App\Console;

use App\Console\Commands\BookingNotifyCommand;
use App\Console\Commands\DeleteOldExpertSchedules;
use App\Console\Commands\UpdateBookingStatus;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    protected $commands = [
        DeleteOldExpertSchedules::class,
        UpdateBookingStatus::class,
        BookingNotifyCommand::class,
    ];

    protected function schedule(Schedule $schedule)
    {
        $schedule->command('app:delete-old-expert-schedules')->dailyAt('00:10');
        $schedule->command('app:update-booking-status')->everyFiveMinutes();
        $schedule->command('app:booking-notify-command')->everyMinute();
    }

    protected function commands()
    {
        $this->load(__DIR__ . '/Commands');
    }
}
