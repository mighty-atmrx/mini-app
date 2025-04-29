<?php

namespace App\Repositories;

use App\Models\Course;
use Illuminate\Database\Eloquent\Collection;

class CourseRepository
{
    protected $model;

    public function __construct(Course $course)
    {
        $this->model = $course;
    }

    public function getAllCourses()
    {
        return $this->model->all();
    }

    public function getExpertCourses(int $expertId): ?Collection
    {
        return $this->model->where('expert_id', $expertId)->with('category')->get();
    }

    public function create(array $data): ?Course
    {
        $course = $this->model->create($data);
        return $course;
    }
}
