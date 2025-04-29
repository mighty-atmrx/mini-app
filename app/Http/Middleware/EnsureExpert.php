<?php

namespace App\Http\Middleware;

use App\Repositories\ExpertRepository;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureExpert
{
    protected $expertRepository;
    public function __construct(ExpertRepository $expertRepository)
    {
        $this->expertRepository = $expertRepository;
    }
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $userId = auth()->id();
        $expert = $this->expertRepository->getExpertByUserId($userId);
        if (!auth()->check() || auth()->user()->role !== 'expert' || !$expert) {
            \Log::error('Expert not found', ['user_id' => $userId]);
            return response()->json(['message' => 'Expert not found'], 404);
        }

        $request->merge(['expert_id' => $expert->id]);
        return $next($request);
    }
}
