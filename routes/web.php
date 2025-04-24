<?php

use App\Http\Controllers\CategoryController;
use App\Telegram\Handler;
use DefStudio\Telegraph\Models\TelegraphBot;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::post('/api/telegram/webhook', function () {
    $bot = TelegraphBot::where('token', env('TELEGRAM_BOT_TOKEN'))->firstOrFail();
    $handler = app(Handler::class);
    return $handler->handle(request(), $bot);
})->name('telegraph.webhook');
