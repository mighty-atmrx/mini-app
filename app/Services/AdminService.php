<?php

namespace App\Services;

use App\Exports\ExpertsExport;
use App\Repositories\ExpertRepository;
use App\Repositories\UserRepository;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\Response;

class AdminService
{
    protected $expertRepository;
    protected $userRepository;

    public function __construct(
        ExpertRepository $expertRepository,
        UserRepository $userRepository,
    ){
        $this->expertRepository = $expertRepository;
        $this->userRepository = $userRepository;
    }

    public function deleteExpert(int $expertId)
    {
        if (auth()->user()->role !== 'admin') {
            \Log::error('User is not an admin.');
            throw new HttpResponseException(response()->json([
                'message' => 'Доступ запрещен.'
            ], Response::HTTP_FORBIDDEN));
        }

        $expert = $this->expertRepository->getExpertById($expertId);
        if (!$expert) {
            \Log::error('Expert not found.', ['expert_id' => $expertId]);
            throw new HttpResponseException(response()->json([
                'message' => 'Эксперт не найден.'
            ], Response::HTTP_NOT_FOUND));
        }

        $this->userRepository->updateUserRole('user', $expert->user_id);

        DB::table('expert_categories')
            ->where('expert_id', $expertId)
            ->delete();

        DB::table('services')
            ->where('expert_id', $expertId)
            ->delete();

        DB::table('experts_schedules')
            ->where('expert_id', $expertId)
            ->delete();

        return $this->expertRepository->delete($expertId);
    }

    public function deleteUser(int $userId)
    {
        if (auth()->user()->role !== 'admin') {
            \Log::error('User is not an admin.');
            throw new HttpResponseException(response()->json([
                'message' => 'Доступ запрещен.'
            ], Response::HTTP_FORBIDDEN));
        }

        $user = $this->userRepository->findUserById($userId);
        if (!$user) {
            \Log::error('User not found.', ['user_id' => $userId]);
            throw new HttpResponseException(response()->json([
                'message' => 'Пользователь не найден.'
            ], Response::HTTP_NOT_FOUND));
        }

        return $this->userRepository->delete($userId);
    }
}
