<?php

namespace App\Services;

use App\Models\Expert;
use App\Repositories\ExpertRepository;
use App\Repositories\ExpertsScheduleRepository;
use Illuminate\Http\Exceptions\HttpResponseException;

class ExpertsScheduleService
{
    protected $expertsScheduleRepository;
    protected $expertService;
    protected $expertRepository;

    public function __construct(
        ExpertsScheduleRepository $expertsScheduleRepository,
        ExpertService $expertService,
        ExpertRepository $expertRepository,
    ){
        $this->expertsScheduleRepository = $expertsScheduleRepository;
        $this->expertService = $expertService;
        $this->expertRepository = $expertRepository;
    }

    public function getMySchedule()
    {
        if (auth()->user()->role !== 'expert'){
            \Log::error('User is not expert', ['user_id' => auth()->id()]);
            throw new HttpResponseException(response()->json([
                'message' => 'Вы не являетесь экспертом.'
            ], 403));
        }

        $expertId = Expert::where('user_id', auth()->id())->first()->id;
        return $this->expertsScheduleRepository->getMySchedule($expertId);
    }

    public function store(array $data)
    {
        if (!$this->expertService->userAlreadyHasExpert()) {
            \Log::error('User has not expert');
            throw new HttpResponseException(response()->json([
                'message' => 'Вы не являетесь экспертом.'
            ], 403));
        }

        $expert = $this->expertRepository->getExpertByUserId(auth()->id());
        $data['expert_id'] = $expert->id;

        return $this->expertsScheduleRepository->create($data);
    }
}
