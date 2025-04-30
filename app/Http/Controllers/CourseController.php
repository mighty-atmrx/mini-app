<?php

namespace App\Http\Controllers;

use App\Repositories\CourseRepository;
use App\Repositories\ExpertRepository;
use App\Services\CourseService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CourseController extends Controller
{
    protected $courseService;
    protected $courseRepository;
    protected $expertRepository;

    public function __construct(
        CourseService  $courseService,
        CourseRepository $courseRepository,
        ExpertRepository $expertRepository
    ){
        $this->courseService = $courseService;
        $this->courseRepository = $courseRepository;
        $this->expertRepository = $expertRepository;
    }

    public function index()
    {
        try {
            $courses = $this->courseRepository->getAllCourses();
            return response()->json($courses);
        } catch (\Exception $e) {
            \Log::error('Courses error: ' . $e->getMessage());
            return response()->json([
                'message' => 'Something went wrong',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function getExpertCourses(int $expertId)
    {
        try {
            $courses = $this->courseRepository->getExpertCourses($expertId);
            return response()->json($courses);
        } catch (\Exception $e) {
            \Log::error('Courses error: ' . $e->getMessage());
            return response()->json([
                'message' => 'Something went wrong',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'title' => 'required|string',
            'description' => 'required|string',
            'price' => 'required|numeric',
            'category_id' => 'required|exists:categories,id',
        ]);

        $data['expert_id'] = $request->input('expert_id');

        DB::beginTransaction();

        try {
            $course = $this->courseRepository->create($data);
            if (!$course) {
                throw new \Exception('Failed to create course');
            }

            DB::commit();

            \Log::info('Course added successfully', [
                'course' => $course
            ]);

            return response()->json([
                'message' => 'Course added successfully',
                'course' => $course
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Course create error', [
                'error' => $e->getMessage(),
                'data' => $data
            ]);
            return response()->json([
                'message' => 'Course not created',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function updateCourse(Request $request, int $courseId)
    {
        $data = $request->validate([
           'title' => 'nullable|string',
           'description' => 'nullable|string',
           'price' => 'nullable|numeric',
           'category_id' => 'nullable|exists:categories,id',
        ]);

        DB::beginTransaction();

        try {
            $course = $this->courseRepository->update($data, $courseId);
            if (!$course) {
                throw new \Exception('Failed to update course');
            }

            DB::commit();

            \Log::info('Course updated successfully');

            return response()->json([
                'message' => 'Course updated successfully',
                'course' => $course
            ]);
        } catch (\Exception $e) {
            \Log::info('Course update error', [
                'course_id' => $courseId,
                'error' => $e->getMessage()
            ]);
            return response()->json([
                'message' => 'Course not updated',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
