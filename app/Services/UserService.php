<?php

namespace App\Services;

use App\Repositories\UserRepository;

class UserService
{
    protected $userRepository;

    public function __construct(UserRepository $userRepository)
    {
        $this->userRepository = $userRepository;
    }

    public function userCreate(array $data, $telegramId)
    {
        $hashedTelegramId = hash('sha256', trim($telegramId));

        $existingUser = $this->userRepository->findByTelegramId($hashedTelegramId);
        if ($existingUser) {
            \Log::warning('User already exists', [
                'telegram_id' => $data['telegram_id'],
                'existing_user_id' => $existingUser->id,
            ]);
            throw new \Exception('Пользователь уже зарегистрирован.');
        }
        $user = $this->userRepository->save($data, $hashedTelegramId);
        return $user;
    }
}
