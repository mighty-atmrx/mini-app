<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\ExpertsSchedule>
 */
class ExpertsScheduleFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        static $expertsIds = [1, 1, 1, 2, 2, 2, 3, 3, 3, 4, 4, 4, 4];
        static $dates = [
            '2025-06-13', '2025-06-13', '2025-06-13', '2025-06-11',
            '2025-06-11', '2025-06-11', '2025-06-12', '2025-06-12',
            '2025-06-12', '2025-06-10', '2025-06-10', '2025-06-13', '2025-06-13'
        ];
        static $times = [
            '11:00:00', '12:00:00', '13:00:00', '15:00:00', '16:00:00',
            '17:00:00', '18:00:00', '19:00:00', '20:00:00', '11:00:00',
            '12:00:00', '13:00:00', '14:00:00'
        ];

        static $index = 0;

        $expertId = $expertsIds[$index % count($expertsIds)];
        $date = $dates[$index % count($dates)];
        $time = $times[$index % count($times)];
        $index++;

        return [
            'expert_id' => $expertId,
            'date' => $date,
            'time' => $time,
        ];
    }
}
