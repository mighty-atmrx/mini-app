<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\ExpertCategory>
 */
class ExpertCategoryFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        static $baseExpertIds = [1, 2];
        static $baseCategoryIds = [1, 2];

        static $index = 0;

        $expertId = $baseExpertIds[$index % count($baseExpertIds)];
        $categoryId = $baseCategoryIds[$index % count($baseCategoryIds)];

        $index++;

        return [
            'expert_id' => $expertId,
            'category_id' => $categoryId,
        ];
    }
}
