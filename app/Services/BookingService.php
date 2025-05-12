<?php

namespace App\Services;

use App\Repositories\ExpertRepository;
use App\Repositories\ExpertsScheduleRepository;
use Illuminate\Http\Exceptions\HttpResponseException;
use Symfony\Component\HttpFoundation\Response;

class BookingService
{
    protected $expertScheduleRepository;
    protected $expertRepository;

    public function __construct(
        ExpertsScheduleRepository $expertScheduleRepository,
        ExpertRepository $expertRepository,
    ){
        $this->expertScheduleRepository = $expertScheduleRepository;
        $this->expertRepository = $expertRepository;
    }

    public function getExpertAvailableBookings(int $expertId)
    {
        $expert = $this->expertRepository->getExpertById($expertId);
        if (!$expert) {
            \Log::error('Expert not found with id ' . $expertId);
            throw new HttpResponseException(response()->json([
                'message' => 'Эксперт не найден.'
            ], Response::HTTP_NOT_FOUND));
        }
        \Log::info('Expert with id ' . $expertId . ' found. Searching for available bookings...');

        $bookings = $this->expertScheduleRepository->getExpertSchedule($expertId);

        if ($bookings->isEmpty()) {
            \Log::error('Available bookings not found with id ' . $expertId);
            throw new HttpResponseException(response()->json([
                'message' => 'Не найдено свободных слотов для этого экперта.'
            ], Response::HTTP_NOT_FOUND));
        }

        \Log::info('Available bookings found.');
        return $bookings;
    }
}
