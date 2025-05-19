<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Favorite>
 */
class FavoriteFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        static $usersIds = [1, 2];
        static $expertsIds = [2, 1];

        static $index = 0;

        $userId = $usersIds[$index % count($usersIds)];
        $expertId = $expertsIds[$index % count($expertsIds)];
        $index++;

        return [
            'user_id' => $userId,
            'expert_id' => $expertId,
        ];
    }
}
