<?php

namespace App\Http\Controllers;

use App\Http\Requests\Expert\StoreExpertRequest;
use App\Http\Requests\Expert\UpdateExpertRequest;
use App\Models\ExpertCategory;
use App\Repositories\ExpertRepository;
use App\Repositories\UserRepository;
use App\Services\ExpertService;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;
use Illuminate\Session\Store;
use Illuminate\Support\Facades\DB;

class ExpertController extends Controller
{
    protected $expertRepository;
    protected $expertCategory;
    protected $expertService;
    protected $userRepository;

    public function __construct(
        ExpertRepository $expertRepository,
        ExpertCategory $expertCategory,
        ExpertService $expertService,
        UserRepository $userRepository,
    ){
        $this->expertRepository = $expertRepository;
        $this->expertCategory = $expertCategory;
        $this->expertService = $expertService;
        $this->userRepository = $userRepository;
    }

    public function index()
    {
        $experts = $this->expertRepository->getAllExperts();
        return response()->json($experts);
    }

    public function getParticularExpert($expertId)
    {
        $expert = $this->expertRepository->getExpertById($expertId);
        if ($expert == null) {
            \Log::warning("Expert not found with id {$expertId}");
            return response()->json([
                'message' => 'Expert not found with id ' . $expertId
            ]);
        }

        $reviews = $expert->reviews()->with('user')->paginate(5);

        return response()->json([
            'expert' => $expert,
            'reviews' => $reviews
        ]);
    }

    public function getExpertsData(Request $request)
    {
        \Log::info('getExpertsData method received', [
            'experts_ids' => $request->query('experts_ids', '')
        ]);
        try {
            $expertsIds = $request->query('experts_ids', '');
            $expertsIds = explode(',', str_replace(['[', ']', ' '], '', $expertsIds));


            $expertsData = [];
            foreach ($expertsIds as $expertId) {
                $expert = $this->expertRepository->getExpertById($expertId);
                if ($expert === null) {
                    $expert = ['expertId' => $expertId, 'error' => 'expert not found'];
                    $expertsData[] = $expert;
                } else {
                   $expertsData[] = $expert;
                }
            }
            return response()->json($expertsData);
        } catch (\Exception $e) {
            \Log::error('getExpertsData Exception', [
                'exception' => $e->getMessage()
            ]);
            return response()->json([
                'message' => 'Не получилось получить данные экспертов',
                'error' => $e->getMessage()
            ]);
        }
    }

    public function store(StoreExpertRequest $request)
    {
        \Log::info('Store method');
        $expert = $this->expertService->userAlreadyHasExpert();
        if ($expert === true) {
            \Log::warning('Expert already created');
            return response()->json([
                'message' => 'Expert already created'
            ], 409);
        }

        $data = $request->validated();
        $data['user_id'] = auth()->id();

        if ($request->hasFile('photo')) {
            $path = $request->file('photo')->store('experts', 'public');
            $data['photo'] = '/storage/' . $path;
        }

        DB::beginTransaction();

        try {
            $expert = $this->expertRepository->create($data);
            $this->userRepository->updateUserRole('expert', auth()->id());
            DB::commit();

            return response()->json([
                'message' => 'Эксперт успешно создан',
                'expert' => $expert
            ], 201);
        } catch (\Throwable $e) {
            DB::rollBack();
            \Log::error('Ошибка при создании эксперта: ' . $e->getMessage());

            return response()->json([
                'message' => 'Ошибка при создании пользователя',
                'error' => $e->getMessage()
            ],  500);
        }
    }

    public function update(UpdateExpertRequest $request, int $expertId)
    {
        $data = $request->validated();

        if ($request->hasFile('photo')) {
            $oldExpert = $this->expertRepository->getExpertById($expertId);

            if ($oldExpert && $oldExpert->photo) {
                \Storage::dist('public')->delete(str_replace('/storage', '', $oldExpert->photo));
            }

            $data['photo'] = '/storage/' . $request->file('photo')->store('experts', 'public');
        }

        DB::beginTransaction();

        try {
            $expert = $this->expertService->updateExpert($data, $expertId);

            if (!$expert) {
                DB::rollBack();
                return response()->json([
                    'message' => 'Не найден эксперт с id = ' . $expertId . ' или доступ запрещен.'
                ]);
            }

            DB::commit();

            return response()->json([
                'message' => 'Экспер успешно обновлен',
                'expert' => $expert
            ], 200);
        } catch (\Throwable $e) {
            DB::rollBack();
            \Log::error('Ошибка обновления эксперта: ' . $e->getMessage());

            return response()->json([
                'message' => 'Не удалось обновить данные эксперта',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
