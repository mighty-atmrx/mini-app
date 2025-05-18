<?php

namespace App\Console;

use App\Console\Commands\DeleteOldExpertSchedules;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    protected $commands = [
        DeleteOldExpertSchedules::class,
    ];

    protected function schedule(Schedule $schedule)
    {
        $schedule->command('app:delete-old-expert-schedules')->dailyAt('00:10');
    }

    protected function commands()
    {
        $this->load(__DIR__ . '/Commands');
    }
}
