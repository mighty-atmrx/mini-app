<?php
namespace App\Http\Middleware;
use Closure;
use Illuminate\Http\Request;

class ForceUtf8
{
    public function handle(Request $request, Closure $next)
    {
        $response = $next($request);
        $response->header('Content-Type', 'text/html; charset=UTF-8');
        return $response;
    }
}
