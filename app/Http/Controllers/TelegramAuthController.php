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
        \Log::info('1111');
        if (!$request->has('initData')) {
            return response()->json(['error' => 'initData is required'], 400);
        }
        \Log::info('22222');

        $userData = $this->telegramAuthService->verifyInitData($request->input('initData'));
        if ($userData) {
            $user = $this->userRepository->findByTelegramId(hash('sha256', (string)$userData['id']));
            if ($user) {
                \Log::info('User authenticated', ['user_id' => $user->id, 'telegram_id' => $userData['id']]);
                try {
                    $token = JWTAuth::fromUser($user);
                } catch (\Exception $e) {
                    \Log::error('JWT error', ['error' => $e->getMessage()]);
                    return response()->json(['error' => 'Authentication failed'], 500);
                }
                return response()->json(['token' => $token]);
            }
            \Log::error('User not found', ['user_id' => $userData['id'], hash('sha256', (string)$userData['id'])]);
            return response()->json(['error' => 'User not found'], 401);
        }
        return response()->json(['error' => 'Invalid initData'], 401);
    }
}
