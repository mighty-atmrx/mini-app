<?php

namespace App\Http\Controllers;

use App\Services\CategoryService;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;

class CategoryController extends Controller
{
    protected $categoryService;

    public function __construct(CategoryService $categoryService)
    {
        $this->categoryService = $categoryService;
    }

    public function index()
    {
        try {
            $response = $this->categoryService->index();
            return response()->json($response);
        } catch (\Exception $e) {
            \Log::error('CategoryController@index - Exception: ' . $e->getMessage());
            return response()->json([
                'message' => 'Не удалось получить данные о категориях'
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function create(Request $request)
    {
        \Log::info('Category create method received.');
        $data = $request->validate([
            'title' => 'required|string',
            'subtitle' => 'required|string',
            'description' => 'nullable|string',
            'position' => 'nullable|integer',
        ]);

        DB::beginTransaction();
        try {
            $this->categoryService->create($data);
            DB::commit();

            \Log::info('Category was created');
            return response()->json([
                'message' => 'Категория создана успешно.'
            ]);
        } catch (HttpResponseException $e) {
            DB::rollBack();
            throw $e;
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Failed to create category: ' . $e->getMessage());
            return response()->json([
                'message' => 'Не удалось создать категорию.'
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function delete(int $categoryId)
    {
        \Log::info('Category delete method received.');

        DB::beginTransaction();
        try {
            $this->categoryService->delete($categoryId);
            DB::commit();

            \Log::info('Category deleted successfully.');
            return response()->json([
                'message' => 'Категория удалена успешно.'
            ]);
        } catch (HttpResponseException $e) {
            DB::rollBack();
            throw $e;
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Failed to delete category: ' . $e->getMessage());
            return response()->json([
                'message' => 'Не удалось удалить категорию.'
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
