<?php

namespace App\Http\Controllers;

use App\Services\ExpertsScheduleService;
use Carbon\Carbon;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;

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
        return response()->json($schedule, Response::HTTP_OK);
    }

    public function store(Request $request)
    {
        \Log::info('Store expert schedule method received');

        DB::beginTransaction();
        try {
            $data = $request->validate([
                'date' => 'required|date_format:d.m.Y',
                'time' => 'required|array',
                'time.*' => ['required', 'date_format:H:i'],
            ]);
            $data['date'] = Carbon::createFromFormat('d.m.Y', $data['date'])->format('Y-m-d');

            foreach ($data['time'] as $time) {
                $this->expertsScheduleService->store([
                    'date' => $data['date'],
                    'time' => $time,
                ]);
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
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function destroy(Request $request)
    {
        \Log::info('Destroy available slot method received');
        $data = $request->validate(['slot_id' => 'required|integer']);

        DB::beginTransaction();
        try {
            $this->expertsScheduleService->delete($data['slot_id']);
            DB::commit();
            \Log::info('An expert removed an available slot.');
            return response()->json([
                'message' => 'Slot removed successfully.'
            ], Response::HTTP_OK);
        } catch (HttpResponseException $e) {
            DB::rollBack();
            throw $e;
        }catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Delete available slot error', ['error' => $e->getMessage()]);
            return response()->json([
                'message' => 'Не удалось удалить слот.'
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
