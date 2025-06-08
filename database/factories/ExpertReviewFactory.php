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
        static $usersIds = [5, 2, 5, 1, 2, 5, 6, 4];
        static $expertsIds = [1, 2, 3, 4, 5, 6, 7, 8];
        static $ratings = [2, 3, 4, 5, 4, 3, 4, 5];
        static $comments = ['Очень хорошо знает свое дело.', 'Профессионал своего дела!', '', '' ,'' ,'' ,'', ''];

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
