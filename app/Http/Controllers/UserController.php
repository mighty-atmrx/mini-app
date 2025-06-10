<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Repositories\BookingRepository;
use App\Repositories\ExpertRepository;
use App\Repositories\UserRepository;
use App\Repositories\UserReviewsRepository;
use App\Services\UserService;
use App\Telegram\InputValidator;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;
use Tymon\JWTAuth\Facades\JWTAuth;


class UserController extends Controller
{
    protected $userRepository;
    protected $userService;
    protected $expertRepository;
    protected $bookingRepository;
    protected $userReviewsRepository;

    public function __construct(
        UserRepository $userRepository,
        UserService $userService,
        ExpertRepository $expertRepository,
        BookingRepository $bookingRepository,
        UserReviewsRepository $userReviewsRepository,
    ){
        $this->userService = $userService;
        $this->userRepository = $userRepository;
        $this->expertRepository = $expertRepository;
        $this->bookingRepository = $bookingRepository;
        $this->userReviewsRepository = $userReviewsRepository;
    }

    public function getFutureBookings()
    {
        try {
            $bookings = $this->userService->getFutureBookings();
            return response()->json($bookings);
        } catch (\Exception $e) {
            \Log::error('getActiveBookings error: ' . $e->getMessage());
            return response()->json([
                'message' => 'Не удалось получить активные записи пользователя'
            ]);
        }
    }

    public function getCompletedBookings()
    {
        try {
            $bookings = $this->userService->getCompletedBookings();
            return response()->json($bookings);
        } catch (\Exception $e) {
            \Log::error('getCompletedBookings error: ' . $e->getMessage());
            return response()->json([
                'message' => 'Не удалось получить записи пройденных курсов пользователя'
            ]);
        }
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'telegram_user_id' => 'required|string',
            'first_name' => 'required|string',
            'last_name' => 'nullable|string',
            'birthdate' => 'required|date',
            'phone' => 'nullable|string',
        ]);

        if ($data['last_name'] === null) {
            $data['last_name'] = '';
        }

        $data['birthdate'] = InputValidator::formatBirthdate($data['birthdate']);

        DB::beginTransaction();
        try {
            $user = $this->userService->userCreate($data, $data['telegram_user_id']);
            DB::commit();
            \Log::info('User created', ['user' => $user]);
            return response()->json($user);
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('User creation failed', ['error' => $e->getMessage()]);
            return response()->json(['User creation failed'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function show(int $userId)
    {
        $user = $this->userRepository->findUserById($userId);
        if (!$user) {
            \Log::error('User not found', ['userId' => $userId]);
            return response()->json(['error' => 'User not found'], Response::HTTP_NOT_FOUND);
        }

        $authUser = JWTAuth::user();

        if ($user->id !== $authUser->id) {
            \Log::error('Unauthorized access attempt', [
                'auth_user_id' => $authUser->id,
                'requested_user_id' => $user->id,
            ]);
            return response()->json(['error' => 'Unauthorized'], Response::HTTP_FORBIDDEN);
        }
        return response()->json($user);
    }

    public function getUserById(int $userId)
    {
        \Log::info('getUserById method received.');
        $user = $this->userRepository->findUserById($userId);
        if (!$user) {
            \Log::error('User not found with id ' . $userId);
            return response()->json([
                'message' => 'Пользователь не найден.'
            ], Response::HTTP_NOT_FOUND);
        }

        $expert = $this->expertRepository->getExpertByUserId(auth()->id());
        if (!$expert) {
            \Log::error('Expert not found with user id ' . auth()->id());
            return response()->json([
                'message' => 'Эксперт не найден.'
            ], Response::HTTP_NOT_FOUND);
        }

        $reviews = $user->reviews()->with('user')->paginate(5);

        $userCountBookingsForThisExpert = $this->bookingRepository
            ->userCountBookingsForThisExpert($expert->id, $userId);

        $expertReviewsForThisUser = $this->userReviewsRepository
            ->expertReviewsForThisUser($expert->id, $userId);

        if ($userCountBookingsForThisExpert > $expertReviewsForThisUser) {
            $expertCanLeaveReview = true;
        } else {
            $expertCanLeaveReview = false;
        }

        \Log::info('User found', ['user' => $user]);
        return response()->json([
            'user' => $user,
            'reviews' => $reviews,
            'expertCanLeaveReview' => $expertCanLeaveReview,
        ]);
    }
}
