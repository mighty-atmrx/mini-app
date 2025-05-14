<?php

namespace App\Repositories;

use App\Models\Booking;

class BookingRepository
{
    public function create(array $data)
    {
        return Booking::create($data);
    }

    public function userIsClientForThisExpert(int $expertId, int $userId): bool
    {
        return Booking::where('expert_id', $expertId)
            ->where('user_id', $userId)
            ->where('status', 'completed')
            ->exists();
    }

    public function userCountBookingsForThisExpert(int $expertId, int $userId): int
    {
        return Booking::where('expert_id', $expertId)
            ->where('user_id', $userId)
            ->count();
    }

}
