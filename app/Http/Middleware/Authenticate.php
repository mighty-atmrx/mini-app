<?php

namespace App\Http\Middleware;

use Illuminate\Auth\Middleware\Authenticate as Middleware;
use Illuminate\Http\Request;

class Authenticate extends Middleware
{
    protected function redirectTo($request)
    {
        \Log::info('Authenticate middleware triggered', [
            'expects_json' => $request->expectsJson(),
            'path' => $request->path(),
        ]);
        if ($request->expectsJson()) {
            return null;
        }
        return route('home');
    }
}
