<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Repositories\UserRepository;
use App\Services\UserService;
use App\Telegram\InputValidator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;
use Tymon\JWTAuth\Facades\JWTAuth;


class UserController extends Controller
{
    protected $userRepository;
    protected $userService;

    public function __construct(
        UserRepository $userRepository,
        UserService $userService
    ){
        $this->userService = $userService;
        $this->userRepository = $userRepository;
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

    public function show(Request $request, $userTelegramId)
    {
        $hashedTelegramId = hash('sha256', (string)$userTelegramId);

        $user = $this->userRepository->findByTelegramId($hashedTelegramId);
        if (!$user) {
            \Log::error('User not found', ['telegram_id' => $userTelegramId]);
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
}
