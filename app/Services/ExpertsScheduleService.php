<?php

namespace App\Services;

use App\Models\Expert;
use App\Models\ExpertsSchedule;
use App\Repositories\ExpertRepository;
use App\Repositories\ExpertsScheduleRepository;
use Illuminate\Http\Exceptions\HttpResponseException;
use Symfony\Component\HttpFoundation\Response;

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
            ], Response::HTTP_FORBIDDEN));
        }

        $expert = Expert::where('user_id', auth()->id())->first();
        if (!$expert) {
            \Log::error('User is not expert', ['user_id' => auth()->id()]);
            throw new HttpResponseException(response()->json([
                'message' => 'Эксперт не найден.'
            ], Response::HTTP_NOT_FOUND));
        }

        return $this->expertsScheduleRepository->getExpertSchedule($expert->id);
    }

    public function store(array $data)
    {
        if (!$this->expertService->userAlreadyHasExpert()) {
            \Log::error('User has not expert');
            throw new HttpResponseException(response()->json([
                'message' => 'Вы не являетесь экспертом.'
            ], Response::HTTP_FORBIDDEN));
        }

        $expert = $this->expertRepository->getExpertByUserId(auth()->id());
        $data['expert_id'] = $expert->id;

        return $this->expertsScheduleRepository->create($data);
    }

    public function delete(int $id)
    {
        $expert  = $this->expertRepository->getExpertByUserId(auth()->id());
        $slot = ExpertsSchedule::find($id);
        if (!$slot) {
            \Log::error('Slot not found.');
            throw new HttpResponseException(response()->json([
                'message' => 'Слот не найден.'
            ], Response::HTTP_NOT_FOUND));
        }

        if (!$expert || $slot->expert_id !== $expert->id) {
            \Log::error('User has not expert or access denied');
            throw new HttpResponseException(response()->json([
                'message' => 'Вы не являетесь экспертом или не имеете доступ к этому действию.'
            ], Response::HTTP_FORBIDDEN));
        }

        return $this->expertsScheduleRepository->delete($slot);
    }
}
