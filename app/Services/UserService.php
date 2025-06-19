<?php

namespace App\Services;

use App\Models\Booking;
use App\Repositories\ExpertRepository;
use App\Repositories\UserRepository;
use App\Repositories\UserReviewsRepository;
use Carbon\Carbon;
use DefStudio\Telegraph\Models\TelegraphChat;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Support\Str;

class UserService
{
    protected $userRepository;
    protected $userReviewsRepository;
    protected $expertRepository;

    public function __construct(
        UserRepository $userRepository,
        UserReviewsRepository $userReviewsRepository,
        ExpertRepository $expertRepository,
    ){
        $this->userRepository = $userRepository;
        $this->userReviewsRepository = $userReviewsRepository;
        $this->expertRepository = $expertRepository;
    }

    public function getFutureBookings()
    {
        $now = Carbon::now();
        $oneHourAgo = $now->copy()->subHour()->toDateTimeString();

        $bookings = Booking::with(['expert.user', 'service'])
            ->where('user_id', auth()->id())
            ->where(function ($query) use ($now, $oneHourAgo) {
                $query->where(function ($q) use ($now, $oneHourAgo) {
                    $q->where('status', 'paid')
                        ->whereNotNull('date')
                        ->whereNotNull('time')
                        ->whereRaw("TRIM(date::text) != ''")
                        ->whereRaw("TRIM(time::text) != ''")
                        ->where(function ($sub) use ($now, $oneHourAgo) {
                            $sub->whereRaw("CONCAT(date::text, ' ', time::text)::timestamp > ?", [$now->toDateTimeString()])
                                ->orWhere(function ($inner) use ($now, $oneHourAgo) {
                                    $inner->whereRaw("CONCAT(date::text, ' ', time::text)::timestamp <= ?", [$now->toDateTimeString()])
                                        ->whereRaw("CONCAT(date::text, ' ', time::text)::timestamp >= ?", [$oneHourAgo]);
                                });
                        });
                })
                    ->orWhere(function ($q) {
                        $q->where('status', 'payment')
                            ->where(function ($sub) {
                                $sub->whereNull('date')
                                    ->orWhereRaw("TRIM(COALESCE(date::text, '')) = ''");
                            })
                            ->where(function ($sub) {
                                $sub->whereNull('time')
                                    ->orWhereRaw("TRIM(COALESCE(time::text, '')) = ''");
                            });
                    });
            })
            ->orderByRaw("
                CASE
                    WHEN date IS NOT NULL AND time IS NOT NULL
                         AND TRIM(COALESCE(date::text, '')) != ''
                         AND TRIM(COALESCE(time::text, '')) != ''
                    THEN CONCAT(date::text, ' ', time::text)::timestamp
                    ELSE NULL
                END ASC NULLS LAST
            ")
            ->get();

        $activeBookings = $bookings->map(function ($booking) {
            $chat = TelegraphChat::whereRaw("encode(sha256(chat_id::text::bytea), 'hex') = ?", [$booking->expert->user->telegram_user_id])->first();
            if ($chat) {
                $expert_username = Str::replaceFirst('[private] ', '', $chat->name);
            } else {
                $expert_username = '';
            }

            return [
                'service_title' => $booking->service->title,
                'date' => $booking->date,
                'time' => $booking->time,
                'expert_first_name' => $booking->expert->first_name,
                'expert_last_name' => $booking->expert->last_name,
                'expert_photo' => $booking->expert->photo,
                'expert_id' => $booking->expert->id,
                'expert_rating' => $booking->expert->rating,
                'expert_username' => $expert_username,
                'expert_phone' => $booking->expert->user->phone,
            ];
        })->all();

        return $activeBookings;
    }

    public function getCompletedBookings()
    {
        $now = Carbon::now()->subHour();
        $bookings = Booking::with(['expert.user', 'service'])
            ->where('user_id', auth()->id())
            ->where('status', 'completed')
            ->whereRaw("(date + time) < ?", [$now->toDateTimeString()])
            ->orderByRaw("CONCAT(date, ' ', time) DESC")
            ->get();

        $completedBookings = $bookings->map(function ($booking) {
            $chat = TelegraphChat::whereRaw("encode(sha256(chat_id::text::bytea), 'hex') = ?", [$booking->expert->user->telegram_user_id])->first();
            if ($chat) {
                $expert_username = Str::replaceFirst('[private] ', '', $chat->name);
            } else {
                $expert_username = '';
            }
            return [
                'service_title' => $booking->service->title,
                'date' => $booking->date,
                'time' => $booking->time,
                'expert_first_name' => $booking->expert->first_name,
                'expert_last_name' => $booking->expert->last_name,
                'expert_photo' => $booking->expert->photo,
                'expert_id' => $booking->expert->id,
                'expert_rating' => $booking->expert->rating,
                'date_of_purchase' => $booking->created_at,
                'expert_username' => $expert_username,
                'expert_phone' => $booking->expert->user->phone,
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
