<?php

namespace App\Repositories;

use App\Models\Service;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\Paginator;

class  ServiceRepository
{
    protected $model;

    public function __construct(Service $service)
    {
        $this->model = $service;
    }

    public function getAllServices(): LengthAwarePaginator
    {
        return $this->model->paginate(10);
    }

    public function getExpertServices(int $expertId): LengthAwarePaginator
    {
        return $this->model->where('expert_id', $expertId)->with('category')->paginate(10);
    }

    public function getServiceById(int $serviceId): ?Service
    {
        return $this->model->findOfFail($serviceId);
    }

    public function create(array $data): Service
    {
        return $this->model->createOrFail($data);
    }

    public function update(array $data, int $serviceId): ?Service
    {
        $service = $this->getServiceById($serviceId);
        if (!$service) {
            return null;
        }
        $service->updateOfFail($data);
        return $service;
    }

    public function delete(int $serviceId): bool
    {
        $service = $this->getServiceById($serviceId);
        if (!$service) {
            return false;
        }
        return $service->deleteOrFail();
    }
}
