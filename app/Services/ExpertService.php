<?php

namespace App\Services;

use App\Repositories\ExpertRepository;
use App\Repositories\ExpertReviewsRepository;

class ExpertService
{
    protected $expertRepository;
    protected $expertReviewsRepository;

    public function __construct(
        ExpertRepository $expertRepository,
        ExpertReviewsRepository $expertReviewsRepository
    ){
        $this->expertRepository = $expertRepository;
        $this->expertReviewsRepository = $expertReviewsRepository;
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
            throw new \Exception('Эксперт не найден.');
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
