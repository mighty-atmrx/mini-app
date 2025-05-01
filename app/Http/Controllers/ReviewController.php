<?php

namespace App\Http\Controllers;

use App\Repositories\ExpertRepository;
use Illuminate\Http\Request;

class ReviewController extends Controller
{
    protected $expertRepository;

    public function __construct(
        ExpertRepository $expertRepository,
    ){
        $this->expertRepository = $expertRepository;
    }

    public function getExpertReviews($expertId)
    {
        $expert = $this->expertRepository->getExpertById($expertId);
    }
}
