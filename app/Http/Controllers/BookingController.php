<?php

namespace App\Http\Controllers;

use App\Services\BookingService;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;

class BookingController extends Controller
{
    protected $bookingService;

    public function __construct(
        BookingService $bookingService,
    ){
        $this->bookingService = $bookingService;
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

            $booking = $this->bookingService->store($data);
            DB::commit();

            \Log::info('Booking added successfully.', [
                'booking' => $booking
            ]);
            return response()->json([
                'message' => 'Запись к эксперту успешно создана.',
                'date' => $booking->date,
                'time' => $booking->time
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
