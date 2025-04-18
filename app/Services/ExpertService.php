<?php

namespace App\Services;

use App\Repositories\ExpertRepository;

class ExpertService
{
    protected $expertRepository;

    public function __construct(ExpertRepository $expertRepository)
    {
        $this->expertRepository = $expertRepository;
    }

    public function createExpert()
    {
        $expert = $this->expertRepository->getExpertByUserId(auth()->id());
        if ($expert) {
            \Log::info('Expert already created');
            return $expert;
        }
        \Log::info('Expert not found. You can create an expert');
        return null;
    }

    public function updateExpert(array $data, int $expertId)
    {
        $expert = $this->expertRepository->getExpertById($expertId);
        if (!$expert || $expert->user_id !== auth()->id()) {
            \Log::error('Expert not found or access denied');
            return null;
        }
        $expert = $this->expertRepository->update($data, $expertId);
        return $expert;
    }
}
