<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Model>
 */
class UserReviewsFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        static $expertsIds = [1, 1, 2];
        static $usersIds = [5, 2, 5];
        static $ratings = [5, 5, 4];
        static $comments = ['Очень приятно иметь дело.', 'Душевный человек!', ''];

        static $index = 0;

        $expertId = $expertsIds[$index % count($expertsIds)];
        $userId = $usersIds[$index % count($usersIds)];
        $rating = $ratings[$index % count($ratings)];
        $comment = $comments[$index % count($comments)];

        $index++;

        return [
            'expert_id' => $expertId,
            'user_id' => $userId,
            'rating' => $rating,
            'comment' => $comment,
        ];
    }
}
