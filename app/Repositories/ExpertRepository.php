<?php

namespace App\Repositories;

use App\Models\Expert;
use Ramsey\Collection\Collection;

class ExpertRepository
{
    protected $model;

    public function __construct(Expert $model)
    {
        $this->model = $model;
    }

    public function getExpertById(int $expertId): ?Expert
    {
        return $this->model->with('categories')->find($expertId);
    }

    public function getExpertByUserId(int $userId): ?Expert
    {
        return $this->model->where('user_id', $userId)->with('categories')->first();
    }

    public function getAllExperts($filter)
    {
        return $this->model->with('categories')->filter($filter)->get();
    }

    public function getCollectionOfExpertsByIds(array $expertIds)
    {
        return $this->model->with('categories')->whereIn('id', $expertIds)->get();
    }

    public function create(array $data): Expert
    {
        return $this->model->create($data);
    }

    public function update(array $data, int $expertId): ?Expert
    {
        $expert = $this->getExpertById($expertId);
        if (!$expert) {
            \Log::error('Expert not found with id="' . $expertId . '"');
            return null;
        }
        $expert->update($data);
        return $expert;
    }
}
