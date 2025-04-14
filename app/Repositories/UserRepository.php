<?php

namespace App\Repositories;

use App\Models\User;

class UserRepository
{
    public function findByTelegramId(string $telegramId): ?User
    {
        $telegramId = trim($telegramId);
        $hashedTelegramId = hash('sha256', $telegramId);

        return User::where('telegram_user_id', $hashedTelegramId)->first();
    }

    public function save(array $data, string $telegramId): User
    {
        $telegramId = trim($telegramId);
        $hashedTelegramId = hash('sha256', $telegramId);

        \Log::info('Saving user with telegram_id', [
            'telegram_id' => $telegramId,
            'hashed_telegram_id' => $hashedTelegramId,
        ]);

        $existingUser = User::where('telegram_user_id', $hashedTelegramId)->first();
        if ($existingUser) {
            \Log::warning('User already exists', [
                'telegram_id' => $telegramId,
                'existing_user_id' => $existingUser->id,
            ]);
            throw new \Exception('Пользователь уже зарегистрирован.');
        }

        \Log::info($data);

        $user = new User([
            'telegram_user_id' => $hashedTelegramId,
            'first_name' => $data['first_name'],
            'last_name' => $data['last_name'],
            'phone' => $data['phone'],
            'birthdate' => $data['birthdate'],
        ]);

        $fillable = $user->getFillable();
        if (!in_array('first_name', $fillable) || !in_array('last_name', $fillable) ||
            !in_array('phone', $fillable) || !in_array('birthdate', $fillable)) {
            throw new \Exception('Ошибка конфигурации модели User.');
        }

        $user->save();

        \Log::info('User saved', [
            'telegram_id' => $telegramId,
            'user_id' => $user->id,
        ]);

        return $user;
    }
}
