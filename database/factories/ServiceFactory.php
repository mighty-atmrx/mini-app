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
        static $baseExpertIds = [1, 1, 1, 2, 2, 3, 3];
        static $baseTitles = [
            'Бесплатный курс по пирожкам',
            'Бесплатный курс по чебурекам',
            'Лучший курс по тому, как печь пирожки прям как у бабушки',
            'Как перестать бояться говорить на публику',
            'Как расширять свою аудиторию',
            'Курс по математике',
            'Курс по обществознанию'
        ];

        static $baseDescriptions = [
            'Бесплатный курс по пирожкам',
            'Бесплатный курс по чебурекам',
            'В этом курсе я научу вас печь пирожки прям как у вашей бабушки.',
            'Рассказываю и даю упражнения на преодоления страха вещания на публику.',
            'Объясняю как расширить свою аудиторию и заставить их внимать каждое Ваше слово.',
            'Курс по математике',
            'Курс по обществознанию'
        ];

        static $basePrices = [0, 0, 9990, 14990, 10000, 3990, 2990];

        static $baseCategoryIds = [1, 1, 1, 1, 1, 1, 1];

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
