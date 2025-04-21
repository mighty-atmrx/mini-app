<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Facades\JWTAuth;

class verifyJWT
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        \Log::info('JWT middleware passed', ['user' => auth()->user()]);

        try {
            $user = JWTAuth::parseToken()->authenticate();
            auth()->setUser($user);
            \Log::info('JWT verified: ', [
                'user_id' => $user ? $user->id : null,
                'token' => $request->bearerToken()
            ]);
        } catch (JWTException $e) {
            \Log::error('JWT error: ', [
                'error' => $e->getMessage(),
                'token' => $request->bearerToken(),
            ]);
            return response()->json(['error' => 'Unauthorized'], 401);
        }
        return $next($request);
    }
}
