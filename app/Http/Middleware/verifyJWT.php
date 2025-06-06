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
        \Log::info('verifyJWT class received', [
            'url' => $request->url(),
            'method' => $request->method(),
        ]);
        try {
            $token = $request->bearerToken();
            if (!$token) {
                \Log::error('No token provided in request');
                return response()->json([
                    'error' => 'Token not provided'
                ], Response::HTTP_UNAUTHORIZED);
            }

            JWTAuth::setToken($token);

            $user = JWTAuth::authenticate();
            if (!$user) {
                \Log::error('User not found for token', ['token' => $token]);
                return response()->json([
                    'error' => 'User not found'
                ], Response::HTTP_UNAUTHORIZED);
            }

            auth()->setUser($user);

            $request->setUserResolver(function () use ($user) {
                return $user;
            });

            \Log::info('JWT verified: ', [
                'user_id' => $user ? $user->id : null,
                'token' => $token,
            ]);
        } catch (JWTException $e) {
            \Log::error('JWT error: ', [
                'error' => $e->getMessage(),
                'token' => $request->bearerToken(),
            ]);
            return response()->json([
                'error' => 'Unauthorized',
                'code' => $e instanceof \Tymon\JWTAuth\Exceptions\TokenInvalidException ? 'invalid_token' : 'jwt_error'
            ], Response::HTTP_UNAUTHORIZED);
        }
        return $next($request);
    }
}
