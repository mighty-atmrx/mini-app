<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\ExpertReview>
 */
class ExpertReviewFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        static $usersIds = [5, 2, 5];
        static $expertsIds = [1, 1, 2];
        static $ratings = [4, 5, 5];
        static $comments = ['Очень хорошо знает свое дело.', 'Профессионал своего дела!', ''];

        static $index = 0;

        $userId = $usersIds[$index % count($usersIds)];
        $expertId = $expertsIds[$index % count($expertsIds)];
        $rating = $ratings[$index % count($ratings)];
        $comment = $comments[$index % count($comments)];

        $index++;

        return [
            'user_id' => $userId,
            'expert_id' => $expertId,
            'rating' => $rating,
            'comment' => $comment
        ];
    }
}
