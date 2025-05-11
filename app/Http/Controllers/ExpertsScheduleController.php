<?php

namespace App\Http\Controllers;

use App\Services\ExpertsScheduleService;
use Carbon\Carbon;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ExpertsScheduleController extends Controller
{
    protected $expertsScheduleService;
    public function __construct(
        ExpertsScheduleService $expertsScheduleService
    ){
        $this->expertsScheduleService = $expertsScheduleService;
    }

    public function getMySchedule()
    {
        \Log::info('getMySchedule method received');
        $schedule = $this->expertsScheduleService->getMySchedule();
        \Log::info('Expert Schedule was received');
        return response()->json($schedule);
    }

    public function store(Request $request)
    {
        \Log::info('store method received');

        DB::beginTransaction();
        try {
            $data = $request->validate([
                'date' => 'required|date_format:d.m.Y',
                'time' => 'required|array',
                'time.*' => ['required', 'date_format:H:i'],
            ]);
            $data['date'] = Carbon::createFromFormat('d.m.Y', $data['date'])->format('Y-m-d');

            foreach ($data['time'] as $time) {
                $data['time'] = $time;
                $this->expertsScheduleService->store($data);
            }
            DB::commit();
            return response()->json([
                'message' => 'График успешно сохранен.'
            ]);
        } catch (HttpResponseException $e) {
            DB::rollBack();
            throw $e;
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Store error', ['error' => $e->getMessage()]);
            return response()->json([
                'message' => 'Не удалось сохранить график.'
            ], 500);
        }
    }
}
