<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Expert>
 */
class ExpertFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        static $baseUserIds = [3, 4];
        static $baseFirstNames = ['Петр', 'Магомед'];
        static $baseLastNames = ['Петров', 'Магомедов'];

        static $baseBiographies = [
            'В детстве я очень любил наблюдать за тем, как бабушка пекет пирожки. Теперь я сам их пеку.',
            'Последние 10 лет активно работаю и развиваюсь в области ораторского мастерства и эффективных коммуникаций.'
        ];

        static $basePhotos = [
            'https://randomuser.me/api/portraits/men/17.jpg',
            'https://randomuser.me/api/portraits/men/64.jpg'
        ];

        static $baseExperiences = [
            '10 лет уже пеку лучшие пирожки в городе.',
            '10 лет вещаю людям про жизнь и то, как важно быть оратором.'
        ];

        static $baseEducations = [
            '3 года в пекарно-кулинарном техникуме, а также у бабушки на каникулах.',
            'Учился в горном институте на оратора 4 года, а после этого практиковался на улице.'
        ];

        static $index = 0;

        $userId = $baseUserIds[$index % count($baseUserIds)];
        $firstName = $baseFirstNames[$index % count($baseFirstNames)];
        $lastName = $baseLastNames[$index % count($baseLastNames)];
        $biography = $baseBiographies[$index % count($baseBiographies)];
        $photo = $basePhotos[$index % count($basePhotos)];
        $experience = $baseExperiences[$index % count($baseExperiences)];
        $education = $baseEducations[$index % count($baseEducations)];

        $index++;

        return [
            'user_id' => $userId,
            'first_name' => $firstName,
            'last_name' => $lastName,
            'biography' => $biography,
            'photo' => $photo,
            'experience' => $experience,
            'education' => $education,
        ];
    }
}
