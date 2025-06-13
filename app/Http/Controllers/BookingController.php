<?php

namespace App\Http\Controllers;

use App\Repositories\ExpertRepository;
use App\Repositories\ServiceRepository;
use App\Services\BookingService;
use Carbon\Carbon;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;

class BookingController extends Controller
{
    protected $bookingService;
    protected $serviceRepository;
    protected $expertRepository;

    public function __construct(
        BookingService $bookingService,
        ServiceRepository $serviceRepository,
        ExpertRepository $expertRepository,
    ){
        $this->bookingService = $bookingService;
        $this->serviceRepository = $serviceRepository;
        $this->expertRepository = $expertRepository;
    }

    public function getAvailableBookings(int $expertId)
    {
        \Log::info('getAvailableBookings method received', ['expertId' => $expertId]);
        try {
            $bookings = $this->bookingService->getExpertAvailableBookings($expertId);
            return response()->json($bookings);
        } catch (HttpResponseException $e) {
            throw $e;
        } catch (\Exception $e) {
            \Log::error('getAvailableBookings with expert id = ' . $expertId . ' error.', [
                'error' => $e->getMessage()
            ]);
            return response()->json([
                'message' => 'Не удалось получить свободные места для записи к специалисту.'
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function store($serviceId)
    {
        \Log::info('Store method in BookingController received.');

        DB::beginTransaction();
        try {
            $data['service_id'] = $serviceId;

            $response = $this->bookingService->store($data);
            DB::commit();

            \Log::info('Booking added successfully.');
            return response()->json($response);
        } catch (HttpResponseException $e) {
            DB::rollBack();
            throw $e;
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Store booking error', ['error' => $e->getMessage()]);
            return response()->json([
                'message' => 'Не удалось создать запись к эксперту.'
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function update(Request $request, $bookingId)
    {
        DB::beginTransaction();
        try {
            $data = $request->validate([
                'date' => 'required|date_format:d.m.Y',
                'time' => 'required|date_format:H:i',
            ]);
            $data['date'] = Carbon::createFromFormat('d.m.Y', $data['date'])->format('Y-m-d');
            $data['status'] = 'paid';

            $this->bookingService->update($data, $bookingId);
            DB::commit();

            \Log::info('Booking updated successfully.', ['bookingId' => $bookingId]);
            return response()->json(['message' => 'Данные записи успешно обновлены.']);
        } catch (HttpResponseException $e) {
            DB::rollback();
            throw $e;
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Update booking error', ['error' => $e->getMessage()]);
            return response()->json([
                'message' => 'Не удалось обновить данные записи.'
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
