<?php

use App\Http\Controllers\BookingController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\ExpertsScheduleController;
use App\Http\Controllers\FavoriteController;
use App\Http\Controllers\ServiceController;
use App\Http\Controllers\ExpertController;
use App\Http\Controllers\TelegramAuthController;
use App\Http\Controllers\UserController;
use App\Telegram\Handler;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Symfony\Component\HttpFoundation\Response;


Route::middleware(['jwt.verify'])->group(function () {
    Route::get('/user', function (Request $request) {
        $user = $request->user();
        \Log::info('GET /api/user attempt', [
            'user_id' => $user ? $user->id : null,
            'token' => $request->bearerToken(),
            'auth_error' => $user ? null : 'No user Authenticated'
        ]);
        if (!$user) {
            return response()->json([
                'error' => 'Unauthorized',
                'code' => 'no_user'
            ], Response::HTTP_UNAUTHORIZED);
        }
        return response()->json([
            'id' => $user->id,
            'first_name' => $user->first_name,
            'last_name' => $user->last_name ?? '',
            'phone' => $user->phone ?? 'Не указан',
            'birthdate' => $user->birthdate ?? 'Не указана',
        ], Response::HTTP_OK);
    });
    Route::get('/users/{telegram_id}', [UserController::class, 'show']);

    Route::get('/categories', [CategoryController::class, 'index'])->name('category.index');

    Route::get('/experts', [ExpertController::class, 'index'])->name('expert.index');
    Route::get('/experts/{expertId}', [ExpertController::class, 'getParticularExpert'])->name('expert.get_particular_expert');
    Route::get('/experts/{expertId}/services', [ServiceController::class, 'getExpertServices'])->name('service.get_expert_services');
    Route::post('/experts', [ExpertController::class, 'store'])->name('expert.store');
    Route::patch('/experts/{expertId}', [ExpertController::class, 'update'])->name('expert.update');

    Route::get('/favorites/experts', [ExpertController::class, 'getExpertsData'])->name('experts.favorites');

    Route::get('/services', [ServiceController::class, 'index'])->name('service.index');
    Route::get('/services/{serviceId}', [ServiceController::class, 'getParticularService'])->name('service.get_particular_service');
    Route::post('/services', [ServiceController::class, 'store'])->middleware('ensure.expert')->name('service.store');
    Route::patch('/services/{serviceId}', [ServiceController::class, 'updateService'])->middleware('ensure.expert')->name('service.update');
    Route::delete('/services/{serviceId}', [ServiceController::class, 'deleteService'])->name('service.delete');

    Route::get('/favorites', [FavoriteController::class, 'index'])->name('favorites.index');
    Route::post('/favorites', [FavoriteController::class, 'store'])->name('favorites.store');
    Route::delete('/favorites', [FavoriteController::class, 'destroy'])->name('favorites.destroy');


    Route::get('/my-available-slots', [ExpertsScheduleController::class, 'getMySchedule'])->name('experts-schedule.getMySchedule');
    Route::post('/my-available-slots', [ExpertsScheduleController::class, 'store'])->name('experts-schedule.store');
    Route::delete('/my-available-slots', [ExpertsScheduleController::class, 'destroy'])->name('experts-schedule.delete');

    Route::get('/bookings/available/{expertId}', [BookingController::class, 'getAvailableBookings'])->name('bookings.get-available-bookings');
    Route::post('/services/{serviceId}/bookings', [BookingController::class, 'store'])->name('bookings.store');
});
Route::post('/telegram/{bot}/webhook', [Handler::class, 'handle'])->middleware('fix_telegraph_bot');
Route::post('auth/telegram', [TelegramAuthController::class, 'authenticate']);
Route::post('auth/telegram/refresh', [TelegramAuthController::class, 'refresh']);

Route::post('/users', [UserController::class, 'store'])->name('user.store');

