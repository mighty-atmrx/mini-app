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

    public function getCourseById(int $courseId): ?Course
    {
        return $this->model->find($courseId);
    }

    public function create(array $data): ?Course
    {
        return $this->model->create($data);
    }

    public function update(array $data, int $courseId): ?Course
    {
        $course = $this->getCourseById($courseId);
        $course->update($data);
        $course->save();
        return $course;
    }
}
