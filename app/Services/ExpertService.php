<?php

namespace App\Services;

use App\Repositories\ExpertRepository;
use App\Repositories\ExpertReviewsRepository;
use App\Repositories\ServiceRepository;
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
