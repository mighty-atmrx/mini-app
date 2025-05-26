<?php

namespace App\Console\Commands;

use Carbon\Carbon;
use DateTimeZone;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class UpdateBookingStatus extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:update-booking-status';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Обновляет статус записей, у которых дата и время меньше текущего момента.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $now = Carbon::now(new DateTimeZone('Asia/Almaty'));

        $count = DB::table('bookings')
            ->where('status', 'paid')
            ->whereRaw("TO_TIMESTAMP(date || ' ' || time, 'YYYY-MM-DD HH24:MI:SS') <= ?", [$now->toDateTimeString()])
            ->update(['status' => 'completed']);

        \Log::info("Статусы завершенных записей успешны обновлены. Обновлено записей: {$count}");
        $this->info("Статусы завершенных записей успешны обновлены. Обновлено записей: {$count}");
    }
}
