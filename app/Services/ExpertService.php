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
}
