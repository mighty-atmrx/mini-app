<?php

namespace App\Http\Controllers;

use App\Services\BookingService;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\Request;
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
                'message' => 'Failed to get available bookings for expert appointment.'
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function store(Request $request)
    {

    }
}
