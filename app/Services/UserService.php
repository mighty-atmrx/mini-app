<?php

namespace App\Services;

use App\Repositories\UserRepository;
use App\Repositories\UserReviewsRepository;
use Illuminate\Http\Exceptions\HttpResponseException;

class UserService
{
    protected $userRepository;
    protected $userReviewsRepository;

    public function __construct(
        UserRepository $userRepository,
        UserReviewsRepository $userReviewsRepository,
    ){
        $this->userRepository = $userRepository;
        $this->userReviewsRepository = $userReviewsRepository;
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

    public function updateUserRating(int $userId)
    {
        \Log::info('Update user rating method received.');
        $user = $this->userRepository->findUserById($userId);
        if (!$user) {
            \Log::error('Failed to find an user when trying to update rating.');
            throw new HttpResponseException(response()->json([
                'message' => 'Пользователь не найден.',
            ]));
        }

        $userReviews = $this->userReviewsRepository->getUserReviews($userId);
        if (!$userReviews) {
            \Log::warning('The user has no feedback.');
            return 0;
        }

        $rating = $userReviews->avg('rating');
        return $this->userRepository->updateUserRating($user, $rating);
    }
}
