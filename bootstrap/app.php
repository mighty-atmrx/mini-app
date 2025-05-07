<?php

use App\Http\Middleware\EnsureExpert;
use App\Http\Middleware\FixTelegraphBot;
use App\Http\Middleware\verifyJWT;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
//        $middleware->append();

//        $middleware->group('api', []);

        $middleware->alias([
            'jwt.verify' => VerifyJWT::class,
            'ensure.expert' => EnsureExpert::class,
            'fix_telegraph_bot' => FixTelegraphBot::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();
