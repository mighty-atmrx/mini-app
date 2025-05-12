<?php

namespace App\Repositories;

use App\Models\ExpertsSchedule;

class ExpertsScheduleRepository
{
    public function getExpertSchedule(int $expertId)
    {
        return ExpertsSchedule::where('expert_id', $expertId)->orderBy('date')->get();
    }

    public function create(array $data)
    {
        return ExpertsSchedule::create($data);
    }

    public function delete(ExpertsSchedule $slot)
    {
        return $slot->deleteOrFail();
    }
}
