<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Repositories\UserRepository;
use App\Services\TelegramAuthService;
use Illuminate\Http\Request;
use Tymon\JWTAuth\Facades\JWTAuth;

class TelegramAuthController extends Controller
{
    protected $telegramAuthService;
    protected $userRepository;

    public function __construct(
        TelegramAuthService $telegramAuthService,
        UserRepository $userRepository
    ){
        $this->telegramAuthService = $telegramAuthService;
        $this->userRepository = $userRepository;
    }
    public function authenticate(Request $request)
    {
        \Log::info('Auth request received', ['input' => $request->all()]);

        try {
            $userData = $this->telegramAuthService->verifyInitData($request->input('initData'));
            if (!$userData) {
                \Log::error('Invalid userData');
                return response()->json(['error' => 'Invalid userData'], 401);
            }

            $telegramId = (string)$userData['id'];
            $hashedTelegramId = hash('sha256', $telegramId);
            \Log::info('Hashed telegramId', ['telegram_id' => $telegramId, 'hashed_telegram_id' => $hashedTelegramId]);
            $user = $this->userRepository->findByTelegramId($hashedTelegramId);
            if (!$user) {
                \Log::error('User not found');
                return response()->json(['error' => 'User not found'], 401);
            }

            $token = JWTAuth::fromUser($user);
            \Log::info('Token', ['user_id' => $user->id, 'token' => substr($token, 0, 20) . '...']);
            return response()->json(['token' => $token]);
        } catch (\Exception $e) {
            \Log::error('Auth error: ', ['error' => $e->getMessage()]);
            return response()->json(['error' => 'Authorization error ' . $e->getMessage()], 401);
        }
    }
}
