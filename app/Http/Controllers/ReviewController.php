<?php

namespace App\Http\Controllers;

use App\Services\ExpertReviewsService;
use App\Services\ExpertService;
use App\Services\UserReviewsService;
use App\Services\UserService;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;

class ReviewController extends Controller
{
    protected $expertReviewsService;
    protected $expertService;
    protected $userReviewsService;
    protected $userService;

    public function __construct(
        ExpertReviewsService $expertReviewsService,
        ExpertService $expertService,
        UserReviewsService $userReviewsService,
        UserService $userService,
    ){
        $this->expertReviewsService = $expertReviewsService;
        $this->expertService = $expertService;
        $this->userReviewsService = $userReviewsService;
        $this->userService = $userService;
    }

    public function storeReviewForExpert(int $expertId, Request $request)
    {
        \Log::info('Store review for expert method received.');
        DB::beginTransaction();
        try {
            $data = $request->validate([
                'rating' => 'required|numeric|min:1|max:5',
                'comment' => 'nullable|string'
            ]);
            $data['user_id'] = auth()->id();
            $data['expert_id'] = $expertId;

            $this->expertReviewsService->storeReviewForExpert($data);
            $this->expertService->updateExpertRating($data['expert_id']);
            DB::commit();

            \Log::info('Expert review has been successfully stored.');
            return response()->json([
                'message' => 'Отзыв об эксперт был успешно опубликован.'
            ]);
        } catch (HttpResponseException $e) {
            DB::rollBack();
            throw $e;
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Store review for expert method error: ' . $e->getMessage());
            return response()->json([
                'message' => 'Не удалось оставить отзыв об эксперте. Попробуйте позже.'
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function storeReviewForUser(int $userId, Request $request)
    {
        \Log::info('Store review for user method received.');

        DB::beginTransaction();
        try {
            $data = $request->validate([
                'rating' => 'required|numeric|min:1|max:5',
                'comment' => 'nullable|string'
            ]);

            $this->userReviewsService->storeReviewForUser($userId, $data);
            $this->userService->updateUserRating($userId);
            DB::commit();

            \Log::info('User review has been successfully stored.');
            return response()->json([
                'message' => 'Отзыв об пользователе успешно опубликован.'
            ]);
        } catch (HttpResponseException $e) {
            DB::rollBack();
            throw $e;
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Store review for user method error: ' . $e->getMessage());
            return response()->json([
                'message' => 'Не удалось оставить отзыв об пользователе. Попробуйте позже.'
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
