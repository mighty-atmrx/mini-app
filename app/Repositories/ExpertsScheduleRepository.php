<?php

namespace App\Repositories;

use App\Models\ExpertsSchedule;

class ExpertsScheduleRepository
{
    public function getMySchedule($expertId)
    {
        return ExpertsSchedule::where('expert_id', $expertId)->get();
    }

    public function create(array $data)
    {
        return ExpertsSchedule::create($data);
    }
}
