<?php

namespace App\Http\Controllers;

use App\Models\Favorite;
use App\Repositories\ExpertRepository;
use App\Services\FavoriteService;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class FavoriteController extends Controller
{
    protected $expertRepository;
    protected $favoriteService;

    public function __construct(
        ExpertRepository $expertRepository,
        FavoriteService $favoriteService
    ){
        $this->expertRepository = $expertRepository;
        $this->favoriteService = $favoriteService;
    }

    public function index()
    {
        try {
            $userId = auth()->id();
            $favorites = Favorite::where('user_id', $userId)->get();
            $expertIds = [];
            foreach ($favorites as $favorite) {
                $expertIds[] = $favorite->expert_id;
            }
            $experts = $this->expertRepository->getCollectionOfExpertsByIds($expertIds);
            return response()->json($experts);
        } catch (\Exception $exception) {
            \Log::error('Get Favorites error: ' . $exception->getMessage());
            return response()->json([
                'message' => 'Не удалось получить данные избранных экспертов.'
            ]);
        }
    }

    public function store(Request $request)
    {
        DB::beginTransaction();
        try {
            $data = $request->validate(['expert_id' => 'required|integer']);
            $data['user_id'] = auth()->id();
            \Log::info('Favorite store method received', ['data' => $data]);

            $this->favoriteService->createFavorite($data);
            DB::commit();

            return response()->json([
                'message' => 'Эксперт был успешно добавлен в избранное.'
            ]);
        } catch (HttpResponseException $e) {
            throw $e;
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Error when adding an expert to favorites:' . $e->getMessage());
            return response()->json([
                'message' => 'Не удалось добавить эксперта в избранное.'
            ]);
        }
    }

    public function destroy(Request $request)
    {

        DB::beginTransaction();
        try {
            $data = $request->validate(['expert_id' => 'required|integer']);
            \Log::info('Destroy method for favorites received', ['data' => $data]);

            $favorite = Favorite::where('user_id', auth()->id())
                ->where('expert_id', $data['expert_id'])->first();

            if (!$favorite) {
                \Log::error('No record with this expert was found', [
                    'expert_id' => $data['expert_id'],
                    'user_id' => auth()->id()
                ]);
                return response()->json([
                    'message' => 'Запись не найдена.'
                ]);
            }

            $favorite->delete();
            DB::commit();
            \Log::info('Expert deleted successfully with id: ' . $data['expert_id']);
            return response()->json([
                'message' => 'Эксперт успешно удален из избранного.'
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Error when deleting an expert to favorites:' . $e->getMessage());
            return response()->json([
                'message' => 'Не удалось удалить эксперта из избранного.'
            ]);
        }
    }
}
