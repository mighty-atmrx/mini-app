<?php

namespace App\Repositories;

use App\Models\Expert;

class ExpertRepository
{
    protected $model;

    public function __construct(Expert $model)
    {
        $this->model = $model;
    }

    public function getExpertById(int $expertId): ?Expert
    {
        return $this->model->findOrFail($expertId);
    }

    public function getExpertByUserId(int $userId): ?Expert
    {
        return $this->model->where('user_id', $userId)->first();
    }

    public function getAllExperts()
    {
        return $this->model->with('categories')->get();
    }

    public function create(array $data): Expert
    {
        return $this->model->create($data);
    }

    public function update(array $data, int $expertId): ?Expert
    {
        $expert = $this->getExpertById($expertId);
        if (!$expert) {
            \Log::error('Expert not found');
            return null;
        }
        $expert->update($data);
        return $expert;
    }
}
