<?php

namespace App\Http\Controllers;

use App\Http\Filters\Filter;
use App\Http\Requests\Expert\StoreExpertRequest;
use App\Http\Requests\Expert\UpdateExpertRequest;
use App\Http\Requests\FilterRequest;
use App\Models\ExpertCategory;
use App\Repositories\BookingRepository;
use App\Repositories\ExpertRepository;
use App\Repositories\ExpertReviewsRepository;
use App\Repositories\UserRepository;
use App\Services\ExpertService;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\URL;
use Symfony\Component\HttpFoundation\Response;

class ExpertController extends Controller
{
    protected $expertRepository;
    protected $expertCategory;
    protected $expertService;
    protected $userRepository;
    protected $expertReviewsRepository;
    protected $bookingRepository;

    public function __construct(
        ExpertRepository $expertRepository,
        ExpertCategory $expertCategory,
        ExpertService $expertService,
        UserRepository $userRepository,
        ExpertReviewsRepository $expertReviewsRepository,
        BookingRepository $bookingRepository,
    ){
        $this->expertRepository = $expertRepository;
        $this->expertCategory = $expertCategory;
        $this->expertService = $expertService;
        $this->userRepository = $userRepository;
        $this->expertReviewsRepository = $expertReviewsRepository;
        $this->bookingRepository = $bookingRepository;
    }

    public function index(FilterRequest $request)
    {
        $data = $request->validated();
        $filter = app()->make(Filter::class, ['queryParams' => array_filter($data)]);
        $experts = $this->expertRepository->getAllExperts($filter);
        return response()->json($experts);
    }

    public function getExpertSelfData()
    {
        try {
            $expert = $this->expertService->getExpertSelfData();
            return response()->json($expert);
        } catch (HttpResponseException $e) {
            throw $e;
        } catch (\Exception $e) {
            \Log::error('Get expert self data error: ' . $e->getMessage());
            return response()->json([
                'message' => 'Не удалось получить данные. Попробуйте позже.'
            ]);
        }
    }

    public function getParticularExpert($expertId)
    {
        $expert = $this->expertRepository->getExpertById($expertId);
        if ($expert == null) {
            \Log::warning("Expert not found with id {$expertId}");
            return response()->json([
                'message' => 'Expert not found with id ' . $expertId
            ], Response::HTTP_NOT_FOUND);
        }

        $reviews = $expert->reviews()->with('user')->paginate(5);

        $userReviewsForThisExpert = $this->expertReviewsRepository
            ->userReviewsForThisExpert($expertId, auth()->id());

        $userCountBookingsOfThisExpert = $this->bookingRepository
            ->userCountBookingsForThisExpert($expertId, auth()->id());

        if ($userCountBookingsOfThisExpert > $userReviewsForThisExpert){
            $userCanLeaveReview = true;
        } else {
            $userCanLeaveReview = false;
        }


        return response()->json([
            'expert' => $expert,
            'reviews' => $reviews,
            'userCanLeaveReview' => $userCanLeaveReview,
        ]);
    }

    public function getExpertsData(Request $request)
    {
        \Log::info('getExpertsData method received', [
            'experts_ids' => $request->query('experts_ids', '')
        ]);
        try {
            $expertsIds = explode(',', str_replace(['[', ']', ' '], '', $request->query('experts_ids', '')));
            $validExpertsIds = array_filter(array_map('intval', $expertsIds));

            if (empty($validExpertsIds)) {
                \Log::warning('Valid experts ids not provided', ['experts_ids' => $expertsIds]);
                return response()->json([
                    'message' => 'Не предоставлены id действительных экспертов.'
                ], Response::HTTP_BAD_REQUEST);
            }

            $expertsData = [$this->expertRepository->getCollectionOfExpertsByIds($expertsIds)];

            return response()->json($expertsData, Response::HTTP_OK);
        } catch (\Throwable $e) {
            \Log::error('getExpertsData Exception', ['exception' => $e]);
            return response()->json([
                'message' => 'Не получилось получить данные экспертов'
            ], Response::HTTP_NOT_FOUND);
        }
    }

    public function getMyServices()
    {
        try {
            $services = $this->expertService->getMyServices();
            return response()->json($services, Response::HTTP_OK);
        } catch (HttpResponseException $e){
            throw $e;
        }catch (\Exception $e) {
            \Log::error('getMyServices Exception', ['exception' => $e->getMessage()]);
            return response()->json([
                'message' => 'Не удалось получить курсы. Попробуйте позже.'
            ], Response::HTTP_NOT_FOUND);
        }
    }

    public function store(StoreExpertRequest $request)
    {
        \Log::info('Store method');
        $expert = $this->expertService->userAlreadyHasExpert();
        if ($expert === true) {
            \Log::warning('Expert already created');
            return response()->json([
                'message' => 'Эксперт уже существует.'
            ], Response::HTTP_CONFLICT);
        }

        $data = $request->validated();
        $data['user_id'] = auth()->id();

        if ($request->hasFile('photo')) {
            $path = $request->file('photo')->store('experts', 'public');
            \Log::info('Photo stored', ['path' => $path, 'full_path' => URL::to('/storage/' . $path, [], env('APP_URL', 'https://bluejay-pretty-clearly.ngrok-free.app'))]);
            $data['photo'] = URL::to('/storage/' . $path, [], env('APP_URL', 'https://bluejay-pretty-clearly.ngrok-free.app'));
        }

        DB::beginTransaction();

        try {
            $expert = $this->expertRepository->create($data);
            $this->userRepository->updateUserRole('expert', auth()->id());
            DB::commit();

            return response()->json([
                'message' => 'Эксперт успешно создан',
                'expert' => $expert
            ], Response::HTTP_CREATED);
        } catch (\Throwable $e) {
            DB::rollBack();
            \Log::error('Ошибка при создании эксперта', ['error' => $e]);

            return response()->json([
                'message' => 'Ошибка при создании пользователя'
            ],  Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function update(UpdateExpertRequest $request, int $expertId)
    {
        \Log::info('Expert update method received');

        DB::beginTransaction();
        $data = $request->validated();

        if ($request->hasFile('photo')) {
            $oldExpert = $this->expertRepository->getExpertById($expertId);

            if ($oldExpert && $oldExpert->photo) {
                $relativePath = ltrim(str_replace('/storage/', '', $oldExpert->photo), '/');
                \Storage::disk('public')->delete($relativePath);
            }

            $data['photo'] = '/storage/' . $request->file('photo')->store('experts', 'public');
        }


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
            ], Response::HTTP_OK);
        } catch (\Throwable $e) {
            DB::rollBack();
            \Log::error('Ошибка обновления эксперта: ' . $e);

            return response()->json([
                'message' => 'Не удалось обновить данные эксперта'
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
