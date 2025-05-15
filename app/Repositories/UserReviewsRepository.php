<?php

namespace App\Repositories;

use App\Models\UserReviews;

class UserReviewsRepository
{
    public function store(array $data)
    {
        return UserReviews::create($data);
    }

    public function expertReviewsForThisUser(int $expertId, int $userId)
    {
        return UserReviews::where('expert_id', $expertId)
            ->where('user_id', $userId)
            ->count();
    }

    public function getUserReviews(int $userId)
    {
        return UserReviews::where('user_id', $userId)->get();
    }
}
