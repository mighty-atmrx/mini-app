<?php

namespace App\Http\Controllers;

use App\Repositories\ExpertRepository;
use App\Services\ExpertReviewsService;
use App\Services\ExpertService;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;

class ReviewController extends Controller
{
    protected $expertReviewsService;
    protected $expertService;

    public function __construct(
        ExpertReviewsService $expertReviewsService,
        ExpertService $expertService,
    ){
        $this->expertReviewsService = $expertReviewsService;
        $this->expertService = $expertService;
    }

    public function storeReviewForExpert($expertId, Request $request)
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


}
