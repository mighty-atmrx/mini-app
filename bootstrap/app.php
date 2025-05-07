<?php

use App\Http\Middleware\EnsureExpert;
use App\Http\Middleware\FixTelegraphBot;
use App\Http\Middleware\verifyJWT;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;

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
        $exceptions->renderable(function (Throwable $e, $request) {
            if ($request->expectsJson()) {
                $status = 500;
                $message = 'Произошла ошибка. Попробуйте позже.';

                if ($e instanceof HttpExceptionInterface) {
                    $status = $e->getStatusCode();
                    if ($status === 404) $message = "Ресурс не найден.";
                    elseif ($status === 403) $message = "Доступ запрещен.";
                    elseif ($status === 401) $message = "Не авторизован.";
                }

                if ($e instanceof AuthenticationException) {
                    $status = 401;
                    $message = "Вы не вошли в систему.";
                }

                logger()->error($e);

                return response()->json([
                    'message' => $message,
                ], $status);
            }
        });
    })
    ->create();
