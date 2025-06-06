<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Repositories\UserRepository;
use App\Services\TelegramAuthService;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Tymon\JWTAuth\Facades\JWTAuth;
use Tymon\JWTAuth\JWT;

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
            $initData = $request->input('initData');
            if (!$initData) {
                \Log::error('No initData provided');
                return response()->json([
                    'error' => 'Не предоставлены данные пользователя.'
                ], Response::HTTP_BAD_REQUEST);
            }
            \Log::info('InitData', ['initData' => $initData]);

            $userData = $this->telegramAuthService->verifyInitData($request->input('initData'));
            if (!$userData) {
                \Log::error('Invalid userData');
                return response()->json([
                    'error' => 'Неверные данные пользователя.'
                ], Response::HTTP_UNAUTHORIZED);
            }

            $telegramId = (string)$userData['id'];
            if (!$telegramId) {
                \Log::error('No telegramId in userData', ['userData' => $userData]);
                return response()->json([
                    'error' => 'Не предоставлен телеграм id пользователя.'
                ], Response::HTTP_UNAUTHORIZED);
            }

            $hashedTelegramId = hash('sha256', $telegramId);
            \Log::info('Hashed telegramId', [
                'telegram_id' => $telegramId,
                'hashed_telegram_id' => $hashedTelegramId
            ]);

            $user = $this->userRepository->findByTelegramId($hashedTelegramId);
            if (!$user) {
                $data = [
                    'first_name' => $userData['first_name'],
                    'last_name' => $userData['last_name'],
                    'phone' => $userData['phone'],
                    'birthdate' => $userData['birthdate'],
                ];
                $user = $this->userRepository->save($data, $hashedTelegramId);
            }

            $accessToken = JWTAuth::fromUser($user);

            \Log::info('Tokens generated', [
                'user_id' => $user->id,
                'access_token' => $accessToken,
            ]);

            return response()->json([
                'access_token' => $accessToken,
                'expires_in' => config('jwt.ttl') * 60,
            ], Response::HTTP_OK);
        } catch (\Exception $e) {
            \Log::error('Auth error: ', ['error' => $e->getMessage()]);
            return response()->json(['error' => 'Не авторизован.'], Response::HTTP_UNAUTHORIZED);
        }
    }
}
