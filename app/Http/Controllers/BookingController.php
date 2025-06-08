<?php

namespace App\Http\Controllers;

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

    public function __construct(
        BookingService $bookingService,
        ServiceRepository $serviceRepository
    ){
        $this->bookingService = $bookingService;
        $this->serviceRepository = $serviceRepository;
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

    public function store(Request $request, $serviceId)
    {
        \Log::info('Store method in BookingController received.');
        DB::beginTransaction();
        try {
            $data = $request->validate([
                'date' => 'required|date_format:d.m.Y',
                'time' => 'required|date_format:H:i',
            ]);
            $data['service_id'] = $serviceId;
            $data['date'] = Carbon::createFromFormat('d.m.Y', $data['date'])->format('Y-m-d');

            $booking = $this->bookingService->store($data);
            $service = $this->serviceRepository->getServiceById($serviceId);
            DB::commit();

            \Log::info('Booking added successfully.', [
                'booking' => $booking
            ]);
            return response()->json([
                'message' => 'Запись к эксперту успешно создана.',
                'date' => $booking->date,
                'time' => $booking->time,
                'service' => $service->title,
                'service_id' => $serviceId,
            ]);
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
}
