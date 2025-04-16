<?php

use App\Http\Controllers\TelegramAuthController;
use App\Telegram\Handler;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;


Route::middleware(['jwt.verify'])->group(function () {
    Route::get('/user', function (Request $request) {
        $user = $request->user();
        \Log::info('GET /api/user attempt', [
            'user' => $user ? $user->toArray() : null,
            'token' => $request->bearerToken(),
            'auth_error' => $user ? null : 'No user Authenticated'
        ]);
        if (!$user) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }
        return $request->user();
    });
});

Route::post('/telegram/webhook', [Handler::class, 'handleUserResponse']);
Route::post('auth/telegram', [TelegramAuthController::class, 'authenticate']);
