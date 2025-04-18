<?php

namespace App\Http\Controllers;

use App\Models\ExpertCategory;
use App\Repositories\ExpertRepository;
use App\Services\ExpertService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ExpertController extends Controller
{
    protected $expertRepository;
    protected $expertCategory;
    protected $expertService;

    public function __construct(
        ExpertRepository $expertRepository,
        ExpertCategory $expertCategory,
        ExpertService $expertService
    ){
        $this->expertRepository = $expertRepository;
        $this->expertCategory = $expertCategory;
        $this->expertService = $expertService;
    }

    public function index()
    {
        $experts = $this->expertRepository->getAllExperts();
        return response()->json($experts);
    }

    public function store(Request $request)
    {
        $expert = $this->expertService->createExpert();
        if ($expert !== null) {
            \Log::warning('Expert already created');
            return response()->json([
                'message' => 'Expert already created',
                'expert' => $expert
            ]);
        }
        
        $request->validate([
           'first_name' => 'required|string',
           'last_name' => 'required|string',
           'biography' => 'required|string',
           'photo' => 'required|image|mimes:jpeg,png,jpg,webp|max:2048',
           'experience' => 'required|string',
           'education' => 'required|string',
        ]);

        $data = [
            'user_id' => auth()->id(),
            'first_name' => $request->input('first_name'),
            'last_name' => $request->input('last_name'),
            'biography' => $request->input('biography'),
            'experience' => $request->input('experience'),
            'education' => $request->input('education'),
        ];

        DB::beginTransaction();

        try {
            if ($request->hasFile('photo')) {
                $path = $request->file('photo')->store('experts', 'public');
                $data['photo'] = '/storage/' . $path;
            }

            $expert = $this->expertRepository->create($data);
            DB::commit();

            return response()->json([
                'message' => 'Эксперт успешно создан',
                'expert' => $expert
            ], 201);
        } catch (\Exception $exception) {
            DB::rollBack();
            return response()->json([
                'message' => 'Ошибка при создании пользователя',
                'error' => $exception->getMessage()
            ],  500);
        }
    }

    public function update(Request $request, int $expertId)
    {
        $data = $request->validate([
            'first_name' => 'nullable|string',
            'last_name' => 'nullable|string',
            'biography' => 'nullable|string',
            'photo' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
            'experience' => 'nullable|string',
            'education' => 'nullable|string',
        ]);

        DB::beginTransaction();

        try {
            if ($request->hasFile('photo')) {
                $oldExpert = $this->expertRepository->getExpertById($expertId);
                if ($oldExpert && $oldExpert->photo && file_exists(public_path($oldExpert->photo))) {
                    unlink(public_path($oldExpert->photo));
                }

                $path = $request->file('photo')->store('experts', 'public');
                $data['photo'] = '/storage/' . $path;
            }

            $expert = $this->expertService->updateExpert($data, $expertId);
            DB::commit();

            return response()->json([
                'message' => 'Экспер успешно обновлен',
                'expert' => $expert
            ], 200);
        } catch (\Exception $exception) {
            DB::rollBack();
            \Log::error('Updating expert error: ' . $exception->getMessage());
            return response()->json([
                'message' => 'Не удалось обновить данные эксперта',
                'error' => $exception->getMessage()
            ], 500);
        }
    }
}
