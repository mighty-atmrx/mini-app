<?php

namespace App\Console\Commands;

use Carbon\Carbon;
use DateTimeZone;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class DeleteOldExpertSchedules extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:delete-old-expert-schedules';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $now = Carbon::now(new DateTimeZone('Asia/Almaty'))->startOfDay();

        DB::table('experts_schedules')
            ->where('date', '<', $now)
            ->delete();

        $this->info('Старые слоты успешно удалены.');
    }
}
