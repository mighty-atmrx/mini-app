<?php

namespace App\Exceptions;

use Illuminate\Auth\AuthenticationException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Throwable;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;

class Handler extends ExceptionHandler
{
    public function render($request, Throwable $e)
    {
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

        return parent::render($request, $e);
    }
}
