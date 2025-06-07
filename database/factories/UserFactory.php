<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\User>
 */
class UserFactory extends Factory
{
    /**
     * The current password being used by the factory.
     */
    protected static ?string $password;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        static $baseTelegramIds = [
            432663193, 592263413, 789456123, 987654321,
            897454223, 987453213, 867444322, 848198932,
            728373442, 230723421
        ];
        static $baseFirstNames = ['Василий', 'Сергей', 'Петр', 'Магомед', 'Игорь', 'Вадим', 'Алексей', 'Бобер', 'Антон', 'Орех'];
        static $baseLastNames = ['Олохов', 'Бурунов', 'Петров', 'Магомедов', 'Игривый', 'Вадимыч', 'Алексеев', 'Бобров', 'Антонов', 'Грецкий'];
        static $baseBirthdates = ['01.01.1991', '02.02.1992', '03.03.1993', '04.04.1995', '05.05.1995', '06.06.1995', '07.07.1995', '08.08.1995', '09.09.1995', '10.09.1995'];
        static $basePhones = ['+77056562323', '+77087674343', '+77012345678', '+77098765432', '+77085554433', '+77053335453', '+77083334473', '+77073434372', '+7701324365', '+77007007070'];
        static $baseRoles = ['expert', 'expert', 'expert', 'expert', 'expert', 'expert', 'expert', 'user', 'user', 'admin'];

        static $index = 0;

        $telegramUserId = hash('sha256', (string)$baseTelegramIds[$index % count($baseTelegramIds)]);
        $firstName = $baseFirstNames[$index % count($baseFirstNames)];
        $lastName = $baseLastNames[$index % count($baseLastNames)];
        $birthdate = $baseBirthdates[$index % count($baseBirthdates)];
        $phone = $basePhones[$index % count($basePhones)];
        $role = $baseRoles[$index % count($baseRoles)];

        $index++;

        return [
            'telegram_user_id' => $telegramUserId,
            'first_name' => $firstName,
            'last_name' => $lastName,
            'birthdate' => $birthdate,
            'phone' => $phone,
            'role' => $role
        ];
    }

    /**
     * Indicate that the model's email address should be unverified.
     */
    public function unverified(): static
    {
        return $this->state(fn (array $attributes) => [
            'email_verified_at' => null,
        ]);
    }
}
