<?php

use App\Http\Controllers\CategoryController;
use App\Http\Controllers\CourseController;
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
            return response()->json(['error' => 'Unauthorized', 'code' => 'no_user'], 401);
        }
        return response()->json([
            'id' => $user->id,
            'first_name' => $user->first_name,
            'last_name' => $user->last_name ?? '',
            'phone' => $user->phone ?? 'Не указан',
            'birthdate' => $user->birthdate ?? 'Не указана',
        ]);
    });
    Route::get('/users/{telegram_id}', [UserController::class, 'show']);

    Route::post('/experts', [ExpertController::class, 'store'])->name('expert.store');
    Route::patch('/experts/{expertId}', [ExpertController::class, 'update'])->name('expert.update');

    Route::post('/services', [CourseController::class, 'store'])->middleware('ensure.expert')->name('course.store');
});

Route::post('/telegram/{bot}/webhook', [Handler::class, 'handle']);
Route::post('auth/telegram', [TelegramAuthController::class, 'authenticate']);
Route::post('auth/telegram/refresh', [TelegramAuthController::class, 'refresh']);

Route::get('/categories', [CategoryController::class, 'index'])->name('category.index');

Route::get('/experts', [ExpertController::class, 'index'])->name('expert.index');
Route::get('/experts/{expertId}', [ExpertController::class, 'getParticularExpert'])->name('expert.get_particular_expert');
Route::get('/experts/{expertId}/services', [CourseController::class, 'getExpertCourses'])->name('course.get_expert_courses');

Route::post('/users', [UserController::class, 'store'])->name('user.store');

Route::get('/services', [CourseController::class, 'index'])->name('course.index');

