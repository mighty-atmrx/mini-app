<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Service>
 */
class ServiceFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        static $baseExpertIds = [1, 2, 2];
        static $baseTitles = [
            'Лучший курс по тому, как печь пирожки прям как у бабушки',
            'Как перестать бояться говорить на публику',
            'Как расширять свою аудиторию',
        ];

        static $baseDescriptions = [
            'В этом курсе я научу вас печь пирожки прям как у вашей бабушки.',
            'Рассказываю и даю упражнения на преодоления страха вещания на публику.',
            'Объясняю как расширить свою аудиторию и заставить их внимать каждое Ваше слово.',
        ];

        static $basePrices = [9990, 14990, 10000];

        static $baseCategoryIds = [1, 2, 2];

        static $index = 0;

        $expertId = $baseExpertIds[$index % count($baseExpertIds)];
        $title = $baseTitles[$index % count($baseTitles)];
        $description = $baseDescriptions[$index % count($baseDescriptions)];
        $price = $basePrices[$index % count($basePrices)];
        $categoryId = $baseCategoryIds[$index % count($baseCategoryIds)];

        $index++;

        return [
            'expert_id' => $expertId,
            'title' => $title,
            'description' => $description,
            'price' => $price,
            'category_id' => $categoryId,
        ];
    }
}
