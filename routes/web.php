<?php

use App\Http\Controllers\CategoryController;
use App\Telegram\Handler;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::post('/telegram/webhook', [Handler::class, 'handleUserResponse']);
