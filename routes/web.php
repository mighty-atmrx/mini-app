<?php

use App\Http\Controllers\CategoryController;
use App\Telegram\Handler;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/api/categories', [CategoryController::class, 'index'])->name('category.index');

Route::post('/telegram/webhook', [Handler::class, 'handleUserResponse']);
