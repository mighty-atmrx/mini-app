<?php

use App\Http\Controllers\CategoryController;
use App\Http\Controllers\ExpertController;
use App\Http\Controllers\TelegramAuthController;
use App\Http\Controllers\UserController;
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
    Route::get('/users/{telegram_id}', [UserController::class, 'show']);
    Route::post('/experts', [ExpertController::class, 'store'])->name('expert.store');
    Route::patch('/experts/{expertId}', [ExpertController::class, 'update'])->name('expert.update');
});

Route::get('/categories', [CategoryController::class, 'index'])->name('category.index');
Route::post('/telegram/webhook', [Handler::class, 'handleUserResponse']);
Route::post('auth/telegram', [TelegramAuthController::class, 'authenticate']);

Route::get('/experts', [ExpertController::class, 'index'])->name('expert.index');
