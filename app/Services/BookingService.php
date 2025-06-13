<?php

namespace App\Services;

use App\Models\Booking;
use App\Models\ExpertsSchedule;
use App\Repositories\BookingRepository;
use App\Repositories\ExpertRepository;
use App\Repositories\ExpertsScheduleRepository;
use App\Repositories\ServiceRepository;
use App\Repositories\UserRepository;
use Carbon\Carbon;
use DefStudio\Telegraph\Models\TelegraphChat;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;

class BookingService
{
    protected $expertScheduleRepository;
    protected $expertRepository;
    protected $serviceRepository;
    protected $bookingRepository;
    protected $userRepository;

    public function __construct(
        ExpertsScheduleRepository $expertScheduleRepository,
        ExpertRepository $expertRepository,
        ServiceRepository $serviceRepository,
        BookingRepository $bookingRepository,
        UserRepository $userRepository
    ){
        $this->expertScheduleRepository = $expertScheduleRepository;
        $this->expertRepository = $expertRepository;
        $this->serviceRepository = $serviceRepository;
        $this->bookingRepository = $bookingRepository;
        $this->userRepository = $userRepository;
    }

    public function getExpertAvailableBookings(int $expertId)
    {
        $expert = $this->expertRepository->getExpertById($expertId);
        if (!$expert) {
            \Log::error('Expert not found with id ' . $expertId);
            throw new HttpResponseException(response()->json([
                'message' => 'Эксперт не найден.'
            ], Response::HTTP_NOT_FOUND));
        }
        \Log::info('Expert with id ' . $expertId . ' found. Searching for available bookings...');

        $bookings = $this->expertScheduleRepository->getExpertSchedule($expertId);

        if ($bookings->isEmpty()) {
            \Log::error('Available bookings not found with id ' . $expertId);
            throw new HttpResponseException(response()->json([
                'message' => 'Не найдено свободных слотов для этого экперта.'
            ], Response::HTTP_NOT_FOUND));
        }

        \Log::info('Available bookings found.');
        return $bookings;
    }

    public function store(array $data)
    {
        $service = $this->serviceRepository->getServiceById($data['service_id']);
        if (!$service) {
            \Log::error('Service not found with id ' . $data['service_id']);
            throw new HttpResponseException(response()->json([
                'message' => 'Service not found.'
            ], Response::HTTP_NOT_FOUND));
        }

        $expert =  $this->expertRepository->getExpertById($service->expert_id);
        if (!$expert) {
            \Log::error('Expert not found with id ' . $data['expert_id']);
            throw new HttpResponseException(response()->json([
                'message' => 'Expert not found.'
            ], Response::HTTP_NOT_FOUND));
        }

        $data['expert_id'] = $expert->id;
        $data['user_id'] = auth()->id();

//        $slot = DB::table('experts_schedules')
//            ->where('expert_id', $expert->id)
//            ->where('date', $data['date'])
//            ->where('time', $data['time'])
//            ->lockForUpdate()
//            ->first();
//
//        if (!$slot) {
//            \Log::error('Slot not found with data or already occupied.');
//            throw new HttpResponseException(response()->json([
//                'message' => 'Слот не найден или уже занят.'
//            ], Response::HTTP_CONFLICT));
//        }

//        if ($service->price == 0) {
//            $data['status'] = 'paid';
//        }

//        ExpertsSchedule::where('expert_id', $expert->id)
//            ->where('date', $data['date'])
//            ->where('time', $data['time'])
//            ->delete();

        $this->bookingRepository->create($data);
        $expert_user = $this->userRepository->findUserById($expert->user_id);
        $expert_phone = $expert_user->phone;
        $chat = TelegraphChat::whereRaw("encode(sha256(chat_id::text::bytea), 'hex') = ?", [$expert_user->telegram_user_id])->first();
        if ($chat) {
            $expert_username = Str::replaceFirst('[private] ', '', $chat->name);
        } else {
            $expert_username = '';
        }

        return [
            'message' => 'Запись к эксперту успешно создана.',
            'expert_name' => $expert->first_name,
            'expert_last_name' => $expert->last_name,
            'expert_tg_username' => $expert_username,
            'expert_phone' => $expert_phone,
            'service' => $service->title,
            'service_id' => $service->id,
        ];
    }

    public function update(array $data, int $bookingId)
    {
        $exists = Booking::where('id', $bookingId)
            ->where('date', $data['date'])
            ->where('time', $data['time'])
            ->whereIn('status', ['payment', 'paid'])
            ->exists();

        if ($exists) {
            \Log::error('Slot is already booked.');
            throw new HttpResponseException(response()->json([
                'message' => 'Слот уже занят.'
            ], Response::HTTP_CONFLICT));
        }

        $booking = $this->bookingRepository->getBookingById($bookingId);

        if (!$booking) {
            \Log::error('Booking not found with id ' . $bookingId);
            throw new HttpResponseException(response()->json([
                'message' => 'Запись не найдена.'
            ], Response::HTTP_NOT_FOUND));
        }

        return $this->bookingRepository->update($booking, $data);
    }
}
