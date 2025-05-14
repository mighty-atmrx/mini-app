<?php

namespace App\Services;

use App\Repositories\BookingRepository;
use App\Repositories\ExpertRepository;
use App\Repositories\ExpertReviewsRepository;
use Illuminate\Http\Exceptions\HttpResponseException;
use Symfony\Component\HttpFoundation\Response;

class ExpertReviewsService
{
    protected $expertReviewsRepository;
    protected $expertRepository;
    protected $bookingRepository;

    public function __construct(
        ExpertReviewsRepository $expertReviewsRepository,
        ExpertRepository $expertRepository,
        BookingRepository $bookingRepository,
    ){
        $this->expertReviewsRepository = $expertReviewsRepository;
        $this->expertRepository = $expertRepository;
        $this->bookingRepository = $bookingRepository;
    }

    public function storeReviewForExpert(array $data)
    {
        $expert = $this->expertRepository->getExpertById($data['expert_id']);
        if (!$expert) {
            \Log::error('Expert not found with id ' . $data['expert_id']);
            throw new HttpResponseException(response()->json([
                'message' => 'Эксперт не найден.'
            ], Response::HTTP_NOT_FOUND));
        }

        $userIsClientForThisExpert = $this->bookingRepository
            ->userIsClientForThisExpert($data['expert_id'], $data['user_id']);
        if (!$userIsClientForThisExpert) {
            \Log::error('The user is not a client for this expert or the service has not yet been provided.');
            throw new HttpResponseException(response()->json([
                'message' => 'Вы не являетесь клиентом этого эксперта или услуга еще не было предоставлена.'
            ], Response::HTTP_FORBIDDEN));
        }

        $userCountBookingsOfThisExpert = $this->bookingRepository
            ->userCountBookingsForThisExpert($data['expert_id'], $data['user_id']);

        $userReviewsForThisExpert = $this->expertReviewsRepository
            ->userReviewsForThisExpert($data['expert_id'], $data['user_id']);

        if ($userCountBookingsOfThisExpert <= $userReviewsForThisExpert) {
            \Log::error('The user has already left review for this expert.');
            throw new HttpResponseException(response()->json([
                'message' => 'Вы уже оставили отзыв данному эксперту.'
            ], Response::HTTP_FORBIDDEN));
        }

        return $this->expertReviewsRepository->store($data);
    }
}
