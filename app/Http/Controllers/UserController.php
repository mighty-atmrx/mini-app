<?php

namespace App\Http\Controllers;

use App\Repositories\UserRepository;
use Illuminate\Http\Request;
use Tymon\JWTAuth\Facades\JWTAuth;


class UserController extends Controller
{
    protected $userRepository;
    public function __construct(UserRepository $userRepository)
    {
        $this->userRepository = $userRepository;
    }

    public function show(Request $request, $userTelegramId)
    {
        $hashedTelegramId = hash('sha256', (string)$userTelegramId);

        $user = $this->userRepository->findByTelegramId($hashedTelegramId);
        if (!$user) {
            \Log::error('User not found', ['telegram_id' => $userTelegramId]);
            return response()->json(['error' => 'User not found'], 404);
        }

        $authUser = JWTAuth::user();

        if ($user->id !== $authUser->id) {
            \Log::error('Unauthorized access attempt', [
                'auth_user_id' => $authUser->id,
                'requested_user_id' => $user->id,
            ]);
            return response()->json(['error' => 'Unauthorized'], 403);
        }
        return response()->json($user);
    }
}
