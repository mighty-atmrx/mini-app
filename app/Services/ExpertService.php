<?php

namespace App\Services;

use App\Models\Booking;
use App\Models\Expert;
use App\Repositories\ExpertRepository;
use App\Repositories\ExpertReviewsRepository;
use App\Repositories\ServiceRepository;
use Carbon\Carbon;
use Illuminate\Http\Exceptions\HttpResponseException;
use Symfony\Component\HttpFoundation\Response;

class ExpertService
{
    protected $expertRepository;
    protected $expertReviewsRepository;
    protected $serviceRepository;

    public function __construct(
        ExpertRepository $expertRepository,
        ExpertReviewsRepository $expertReviewsRepository,
        ServiceRepository $serviceRepository,
    ){
        $this->expertRepository = $expertRepository;
        $this->expertReviewsRepository = $expertReviewsRepository;
        $this->serviceRepository = $serviceRepository;
    }

    public function getExpertSelfData()
    {
        $expert = $this->expertRepository->getExpertByUserId(auth()->id());
        if (!$expert) {
            \Log::error('Expert not found with user id ' . auth()->id());
            throw new HttpResponseException(response()->json([
                'message' => 'Эксперт не найден'
            ], Response::HTTP_NOT_FOUND));
        }

        return $expert;
    }

    public function userAlreadyHasExpert()
    {
        $expert = $this->expertRepository->getExpertByUserId(auth()->id());

        if ($expert) {
            \Log::info('Expert already created');
            return true;
        }

        return false;
    }

    public function getMyServices()
    {
        $expert = $this->expertRepository->getExpertByUserId(auth()->id());
        if (!$expert) {
            \Log::error('Expert not found with user id ' . auth()->id());
            throw new HttpResponseException(response()->json([
                'message' => 'Вы не являетесь экспертом или эксперт не найден.'
            ], Response::HTTP_NOT_FOUND));
        }

        return $this->serviceRepository->getExpertServices($expert->id);
    }

    public function getFutureBookings()
    {
        $expert = Expert::where('user_id', auth()->id())->first();
        if (!$expert) {
            \Log::error('Expert not found with user id ' . auth()->id());
            throw new HttpResponseException(response()->json([
                'message' => 'Эксперт не найден'
            ], Response::HTTP_NOT_FOUND));
        }

        $now = Carbon::now();
        $oneHourAgo = $now->copy()->subHour()->toDateTimeString();


        $bookings = Booking::with(['user'])
            ->where('expert_id', $expert->id)
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
            return [
                'user_rating' => $booking->user->rating,
                'user_phone' => $booking->user->phone,
                'date' => $booking->date,
                'time' => $booking->time,
                'user_id' => $booking->user_id,
                'date_of_purchase' => $booking->created_at,
            ];
        })->all();

        return $activeBookings;
    }

    public function getCompletedBookings()
    {
        $expert = Expert::where('user_id', auth()->id())->first();
        if (!$expert) {
            \Log::error('Expert not found with user id ' . auth()->id());
            throw new HttpResponseException(response()->json([
                'message' => 'Эксперт не найден'
            ], Response::HTTP_NOT_FOUND));
        }

        $now = Carbon::now()->subHour();
        $bookings = Booking::with(['user'])
            ->where('expert_id', $expert->id)
            ->where('status', 'completed')
            ->whereRaw("(date + time) < ?", [$now->toDateTimeString()])
            ->orderByRaw("CONCAT(date, ' ', time) DESC")
            ->get();

        $completedBookings = $bookings->map(function ($booking) {
            return [
                'user_rating' => $booking->user->rating,
                'user_phone' => $booking->user->phone,
                'date' => $booking->date,
                'time' => $booking->time,
                'user_id' => $booking->user_id,
                'date_of_purchase' => $booking->created_at,
            ];
        })->all();

        return $completedBookings;
    }

    public function updateExpert(array $data, int $expertId)
    {
        $expert = $this->expertRepository->getExpertById($expertId);
        if (!$expert || $expert->user_id !== auth()->id()) {
            \Log::error('Expert not found or access denied');
            throw new \Exception('Эксперт не найден или доступ запрещен.');
        }
        return $this->expertRepository->update($data, $expertId);
    }

    public function updateExpertRating(int $expertId)
    {
        \Log::info('Update expert rating method received.');
        $expert = $this->expertRepository->getExpertById($expertId);
        if (!$expert) {
            \Log::error('Failed to find an expert when trying to update rating.');
            throw new HttpResponseException(response()->json([
                'message' => 'Эксперт не найден.'
            ]));
        }

        $expertsReviews = $this->expertReviewsRepository->getExpertReviews($expertId);
        if (!$expertsReviews) {
            \Log::warning('The expert has no feedback.');
            return 0;
        }

        $rating = $expertsReviews->avg('rating');
        return $this->expertRepository->updateExpertRating($expert, $rating);
    }
}
