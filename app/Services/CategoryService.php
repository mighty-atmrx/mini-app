<?php

namespace App\Services;

use App\Models\Booking;
use App\Models\Expert;
use App\Models\ExpertReview;
use App\Models\User;
use App\Models\UserReviews;
use App\Repositories\CategoryRepository;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;

class CategoryService
{
    protected $categoryRepository;
    protected $getPendingReviews;

    public function __construct(
        CategoryRepository         $categoryRepository,
        GetPendingReviews          $getPendingReviews,
    ){
        $this->categoryRepository = $categoryRepository;
        $this->getPendingReviews = $getPendingReviews;
    }

    public function index()
    {
        $categories = $this->categoryRepository->getAllCategories();
        $user = auth()->user();
        $pendingReviews = [];

        if (in_array($user->role, ['user', 'admin'])){
            $pendingReviews = $this->getPendingReviews->forUser($user);
        }

        if ($user->role === 'expert') {
            $expert = Expert::where('user_id', $user->id)->first();
            if ($expert) {
                $pendingReviews = array_merge(
                    $this->getPendingReviews->forExpert($expert),
                    $this->getPendingReviews->forUser($user)
                );
            } else {
                \Log::error('Expert not found with user_id ' . $user->id);
            }
        }

        return [
            'categories' => $categories,
            'user_role' => $user->role,
            'pending_reviews' => $pendingReviews
        ];
    }

    public function create(array $data)
    {
        if (auth()->user()->role !== 'admin') {
            \Log::error('User is not an admin.', ['user_id' => auth()->id()]);
            throw new HttpResponseException(response()->json([
                'message' => 'Доступ запрещен.'
            ], Response::HTTP_FORBIDDEN));
        }

        $categoryByTitle = $this->categoryRepository->getCategoryByTitle($data['title']);
        if ($categoryByTitle) {
            \Log::error('Category already exists.', ['category_id' => $categoryByTitle->id]);
            throw new HttpResponseException(response()->json([
                'message' => 'Категория уже существует.'
            ]));
        }

        $categoryBySubtitle = $this->categoryRepository->getCategoryBySubtitle($data['subtitle']);
        if ($categoryBySubtitle) {
            \Log::error('Category already exists.', ['category_id' => $categoryBySubtitle->id]);
            throw new HttpResponseException(response()->json([
                'message' => 'Категория уже существует.'
            ]));
        }

        if (!isset($data['position'])) {
            $data['position'] = DB::table('categories')->max('position') + 1;
        }

        DB::table('categories')
            ->where('position', '>=', $data['position'])
            ->increment('position');

        return $this->categoryRepository->create($data);
    }

    public function delete(int $categoryId)
    {
        if (auth()->user()->role !== 'admin') {
            \Log::error('User is not an admin.', ['user_id' => auth()->id()]);
            throw new HttpResponseException(response()->json([
                'message' => 'Доступ запрещен.'
            ], Response::HTTP_FORBIDDEN));
        }

        $category = $this->categoryRepository->getCategoryById($categoryId);
        if (!$category) {
            \Log::error('Category not found.', ['category_id' => $categoryId]);
            throw new HttpResponseException(response()->json([
                'message' => 'Категория не найдена.'
            ], Response::HTTP_NOT_FOUND));
        }

        DB::table('categories')
            ->where('position', '>=', $category->position)
            ->decrement('position');

        return $this->categoryRepository->delete($categoryId);
    }
}
