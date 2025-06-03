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

    public function __construct(CategoryRepository $categoryRepository)
    {
        $this->categoryRepository = $categoryRepository;
    }

    public function index()
    {
        $categories = $this->categoryRepository->getAllCategories();
        $user = auth()->user();

        if ($user->role == 'user' || $user->role == 'admin'){
            $bookings = Booking::where('user_id', $user->id)
                ->where('status', 'completed')
                ->get();

            $groupedBookings = $bookings->groupBy('expert_id');
            $pendingReviews = [];

            foreach ($groupedBookings as $expertId => $bookings) {
                $totalBookings = $bookings->count();

                $reviewsCount = UserReviews::where('user_id', $user->id)
                    ->where('expert_id', $expertId)
                    ->count();

                if ($reviewsCount < $totalBookings) {
                    $expert = Expert::find($expertId);
                    if ($expert) {
                        $pendingReviews[] = [
                            'id' => $expertId,
                            'first_name' => $expert->first_name,
                            'last_name' => $expert->last_name,
                            'photo' => $expert->photo,
                        ];
                    }
                }
            }
        } elseif ($user->role == 'expert') {
            $expert = Expert::where('user_id', $user->id)->first();
            if (!$expert) {
                \Log::error('Expert not found with user_id ' . $user->id);
                $response = [
                    'categories' => $categories,
                    'user_role' => 'Expert not found',
                    'pending_reviews' => [],
                    ];
                return $response;
            }

            $bookings = Booking::where('expert_id', $expert->id)
                ->where('status', 'completed')
                ->get();

            $groupedBookings = $bookings->groupBy('user_id');
            $pendingReviews = [];

            foreach ($groupedBookings as $userId => $bookings) {
                $totalBookings = $bookings->count();

                $reviewsCount = ExpertReview::where('expert_id', $expert->id)
                    ->where('user_id', $userId)
                    ->count();

                if ($reviewsCount < $totalBookings) {
                    $userForReview = User::find($userId);
                    if ($user) {
                        $pendingReviews[] = [
                            'id' => $userId,
                            'first_name' => $userForReview->first_name,
                            'last_name' => $userForReview->last_name,
                        ];
                    }
                }
            }
        }

        $response = [
            'categories' => $categories,
            'user_role' => $user->role,
            'pending_reviews' => $pendingReviews
        ];
        return $response;
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
