<?php

namespace App\Services;

use App\Models\Booking;
use App\Models\Expert;
use App\Models\Service;
use App\Models\User;
use App\Repositories\UserRepository;
use App\Repositories\UserReviewsRepository;
use Carbon\Carbon;
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

    public function getFutureBookings()
    {
        $now = Carbon::now();
        $bookings = Booking::with(['expert.user', 'service'])
            ->where('user_id', auth()->id())
            ->where('status', 'paid')
            ->whereRaw("(date + time) > ?", [$now])
            ->orderByRaw("CONCAT(date, ' ', time) ASC")
            ->get();

        $activeBookings = $bookings->map(function ($booking) {
            return [
                'service_title' => $booking->service->title,
                'date' => $booking->date,
                'time' => $booking->time,
                'expert_first_name' => $booking->expert->first_name,
                'expert_last_name' => $booking->expert->last_name,
                'expert_photo' => $booking->expert->photo,
                'expert_phone' => $booking->expert->user->phone,
                'expert_id' => $booking->expert->id,
                'expert_rating' => $booking->expert->rating,
            ];
        })->all();

        return $activeBookings;
    }

    public function getCompletedBookings()
    {
        $now = Carbon::now();
        $bookings = Booking::with(['expert.user', 'service'])
            ->where('user_id', auth()->id())
            ->where('status', 'completed')
            ->whereRaw("(date + time) < ?", [$now])
            ->orderByRaw("CONCAT(date, ' ', time) DESC")
            ->get();

        $completedBookings = $bookings->map(function ($booking) {
            return [
                'service_title' => $booking->service->title,
                'date' => $booking->date,
                'time' => $booking->time,
                'expert_first_name' => $booking->expert->first_name,
                'expert_last_name' => $booking->expert->last_name,
                'expert_photo' => $booking->expert->photo,
                'expert_phone' => $booking->expert->user->phone,
                'expert_id' => $booking->expert->id,
                'expert_rating' => $booking->expert->rating,
                'date_of_purchase' => $booking->created_at,
            ];
        })->all();

        return $completedBookings;
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
