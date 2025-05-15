<?php

namespace App\Repositories;

use App\Models\User;

class UserRepository
{
    public function findByTelegramId(string $telegramId): ?User
    {
        $telegramId = trim($telegramId);
        \Log::info('User repository', ['user' => $telegramId]);

        return User::where('telegram_user_id', $telegramId)->first();
    }

    public function findUserById(int $userId): ?User
    {
        return User::findOrFail($userId);
    }

    public function save(array $data, string $hashedTelegramId): User
    {
        \Log::info('Saving user with telegram_id', [
            'hashed_telegram_id' => $hashedTelegramId,
        ]);

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

        $user->saveOrFail();

        \Log::info('User saved', [
            'user_id' => $user->id,
        ]);
        return $user;
    }

    public function updateUserRole(string $role, int $userId)
    {
        $user = User::findOrFail($userId);
        $user->role = $role;
        $user->saveOrFail();
    }

    public function updateUserRating(User $user, float $rating)
    {
        $user->rating = $rating;
        $user->saveOrFail();
        return $user;
    }
}
