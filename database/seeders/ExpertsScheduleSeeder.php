<?php

namespace Database\Seeders;

use App\Models\ExpertsSchedule;
use Illuminate\Database\Seeder;

class ExpertsScheduleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        ExpertsSchedule::factory()->count(6)->create();
    }
}
