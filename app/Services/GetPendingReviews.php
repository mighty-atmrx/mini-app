<?php

namespace App\Services;

use App\Models\Booking;
use App\Models\Expert;
use App\Models\ExpertReview;
use App\Models\User;
use App\Models\UserReviews;

class GetPendingReviews
{
    public function forUser(User $user)
    {
        $bookings = Booking::where('user_id', $user->id)
            ->where('status', 'completed')
            ->get()
            ->groupBy('expert_id');

        $pending = [];

        foreach ($bookings as $expertId => $grouped) {
            $total = $grouped->count();

            $given = ExpertReview::where('user_id', $user->id)
                ->where('expert_id', $expertId)
                ->count();

            \Log::info('LOG: total = ' . $total . ', given = ' . $given);

            if ($given < $total) {
                $expert = Expert::find($expertId);
                if ($expert) {
                    $pending[] = [
                        'expert_id' => $expertId,
                        'first_name' => $expert->first_name,
                        'last_name' => $expert->last_name,
                        'photo' => $expert->photo,
                        'role' => 'Эксперт'
                    ];
                }
            }
        }

        return $pending;
    }

    public function forExpert($expert)
    {
        $bookings = Booking::where('expert_id', $expert->id)
            ->where('status', 'completed')
            ->get()
            ->groupBy('user_id');

        $pending = [];

        foreach ($bookings as $userId => $grouped) {
            $total = $grouped->count();
            $given = UserReviews::where('expert_id', $expert->id)
                ->where('user_id', $userId)
                ->count();

            if ($given < $total) {
                $user = User::find($userId);
                if ($user) {
                    $pending[] = [
                        'user_id' => $userId,
                        'first_name' => $user->first_name,
                        'last_name' => $user->last_name,
                        'role' => 'Клиент'
                    ];
                }
            }
        }

        return $pending;
    }
}
