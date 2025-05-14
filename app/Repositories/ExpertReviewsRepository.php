<?php

namespace App\Repositories;

use App\Models\ExpertReview;

class ExpertReviewsRepository
{
    public function store(array $data)
    {
        return ExpertReview::create($data);
    }

    public function getExpertReviews($expertId)
    {
        return ExpertReview::where('expert_id', $expertId)->get();
    }

    public function userReviewsForThisExpert(int $expertId, int $userId): int
    {
        return ExpertReview::where('expert_id', $expertId)
            ->where('user_id', $userId)
            ->count();
    }
}
