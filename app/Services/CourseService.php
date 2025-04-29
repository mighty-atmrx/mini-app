<?php

namespace App\Services;

use App\Models\Course;
use App\Repositories\CourseRepository;
use App\Repositories\ExpertRepository;

class CourseService
{
    protected $courseRepository;
    protected $expertRepository;

    public function __construct(
        CourseRepository $courseRepository,
        ExpertRepository $expertRepository
    ){
        $this->courseRepository = $courseRepository;
        $this->expertRepository = $expertRepository;
    }
}
