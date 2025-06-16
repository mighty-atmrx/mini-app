<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Booking>
 */
class BookingFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        static $expertsIds = [1, 2, 1, 2, 1, 2];
        static $servicesIds = [1, 2, 1, 3, 1, 2];
        static $usersIds = [1, 2, 5, 1, 2, 5];
        static $dates = ['2025-06-07', '2025-06-08', '2025-06-07', '2025-06-08', '2025-06-07', '2025-06-08'];
        static $times = ['11:00:00', '15:00:00', '12:00:00', '16:00:00', '13:00:00', '17:00:00'];
        static $statuses = ['payment', 'paid', 'completed', 'rejected', 'completed', 'completed'];

        static $index = 0;

        $expertId = $expertsIds[$index % count($expertsIds)];
        $serviceId = $servicesIds[$index % count($servicesIds)];
        $userId = $usersIds[$index % count($usersIds)];
        $date = $dates[$index % count($dates)];
        $time = $times[$index % count($times)];
        $status = $statuses[$index % count($statuses)];

        $index++;

        return [
            'expert_id' => $expertId,
            'service_id' => $serviceId,
            'user_id' => $userId,
            'date' => $date,
            'time' => $time,
            'status' => $status,
        ];
    }
}
