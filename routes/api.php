<?php

use App\Http\Controllers\TelegramAuthController;
use App\Telegram\Handler;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;


Route::middleware('auth:api')->group(function () {
    Route::get('/user', function (Request $request) {
        return $request->user() ?: response()->json(['error' => 'Unauthorized.'], 401);
    });
});
Route::post('/telegram/webhook', [Handler::class, 'handleUserResponse']);
Route::post('auth/telegram', [TelegramAuthController::class, 'authenticate']);
