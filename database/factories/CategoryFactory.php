<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\=Category>
 */
class CategoryFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        static $categories = [
            'Личностный рост',
            'Психология и коучинг',
            'Отношения и коммуникация',
            'Духовные практики',
            'Эзотерика и метафизика',
            'Философия и мировоззрение',
            'Тело и сознание',
            'Финансовая грамотность и карьера',
            'Здоровье и благополучие',
            'Творческий потенциал',
            'Осознанное родительство',
            'Лидерство и социальное влияние',
            'Биохакинг и долголетие',
            'Эмоциональный интеллект',
            'Арт терапия'
        ];

        static $descriptions = [
            '(цели, дисциплина, продуктивность)',
            '(самооценка, уверенность, страхи)',
            '(семья, переговоры)',
            '(медитации, чакры, осознанность)',
            '(нумерология, астрология, таро)',
            '(поиск смысла жизни, самопознание)',
            '(йога, цигун, телесные практики)',
            '(деньги, инвестиции, работа)',
            '(питание, психосоматика, гормоны)',
            '(арт-терапия, музыка, креативное мышление)',
            '(воспитание детей, баланс работы и семьи)',
            '(управление людьми, личный бренд)',
            '(здоровье, энерго-управление, омоложение)',
            '(управление стрессом, работа с эмоциями)',
            ''
        ];

        static $index = 0;
        $category = $categories[$index % count($categories)];
        $index++;

        static $descriptionIndex = 0;
        $description = $descriptions[$descriptionIndex % count($descriptions)];
        $descriptionIndex++;

        return [
            'category' => $category,
            'description' => $description,
        ];
    }
}
