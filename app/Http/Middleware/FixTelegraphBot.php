<?php

namespace App\Http\Middleware;

use Closure;
use DefStudio\Telegraph\Facades\Telegraph;
use DefStudio\Telegraph\Models\TelegraphBot;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class FixTelegraphBot
{
    public function handle(Request $request, Closure $next)
    {
        Log::info('Middleware started', ['path' => $request->path(), 'method' => $request->method()]);

        $token = $request->route('bot')->token;
        Log::info('Checking bot with token', ['token' => $token]);

        $bot = TelegraphBot::where('token', $token)->first();

        if (!$bot) {
            Log::error('Bot not found', ['token' => $token]);
            return response('Bot not found', Response::HTTP_NOT_FOUND);
        }

        $request->merge(['bot' => $bot]);
        Log::info('Fixed bot in middleware', ['bot_id' => $bot->id]);

        Telegraph::bot($bot);
        Log::info('Bot set in Telegraph', ['bot_id' => $bot->id]);

        return $next($request);
    }
}
