<?php

namespace App\Http\Controllers;

use App\Exports\ExpertsExport;
use App\Exports\UsersExport;
use App\Services\AdminService;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\Response;

class AdminController extends Controller
{
    protected $adminService;

    public function __construct(AdminService $adminService)
    {
        $this->adminService = $adminService;
    }

    public function deleteExpert(int $expertId)
    {
        \Log::info('Admin delete expert method received.');

        DB::beginTransaction();
        try {
            $this->adminService->deleteExpert($expertId);
            DB::commit();

            \Log::info('Expert deleted successfully.');
            return response()->json([
                'message' => 'Эксперт успешно удален.'
            ]);
        } catch (HttpResponseException $e) {
            DB::rollBack();
            throw $e;
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Admin delete expert error.', ['error' => $e->getMessage()]);
            return response()->json([
                'message' => 'Не удалось удалить экперта.'
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function deleteUser(int $userId)
    {
        \Log::info('Admin delete user method received.');

        DB::beginTransaction();
        try {
            $this->adminService->deleteUser($userId);
            DB::commit();

            \Log::info('User deleted successfully.');
            return response()->json([
                'message' => 'Пользователь успешно удален.'
            ]);
        } catch (HttpResponseException $e) {
            DB::rollBack();
            throw $e;
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Admin delete user error.', ['error' => $e->getMessage()]);
            return response()->json([
                'message' => 'Не удалось удалить пользователя.'
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function exportExpertsToExcel()
    {
        \Log::info('Admin export experts to excel method received.');

        try {
            if (auth()->user()->role !== 'admin') {
                \Log::error('User is not an admin.');
                return response()->json([
                    'message' => 'Доступ запрещен.'
                ], Response::HTTP_FORBIDDEN);
            }

            return Excel::download(new ExpertsExport, 'experts.xlsx');
        } catch (\Exception $e) {
            \Log::error('Admin export experts to excel error.', ['error' => $e->getMessage()]);
            return response()->json([
                'message' => 'Не удалось выгрузить список всех экспертов.'
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function exportUsersToExcel()
    {
        \Log::info('Admin export users to excel method received.');

        try {
            if (auth()->user()->role !== 'admin') {
                \Log::error('User is not an admin.');
                return response()->json([
                    'message' => 'Доступ запрещен.'
                ], Response::HTTP_FORBIDDEN);
            }

            return Excel::download(new UsersExport, 'users.xlsx');
        } catch (\Exception $e) {
            \Log::error('Admin export users to excel error.', ['error' => $e->getMessage()]);
            return response()->json([
                'message' => 'Не удалось выгрузить данные всех пользователей.'
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
