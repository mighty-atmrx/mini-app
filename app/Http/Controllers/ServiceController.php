<?php

namespace App\Http\Controllers;

use App\Http\Filters\Filter;
use App\Http\Requests\FilterRequest;
use App\Http\Requests\Service\StoreServiceRequest;
use App\Http\Requests\Service\UpdateServiceRequest;
use App\Http\Resources\ServiceResource;
use App\Models\ExpertCategory;
use App\Repositories\ServiceRepository;
use App\Repositories\ExpertRepository;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;

class ServiceController extends Controller
{
    use AuthorizesRequests;

    protected $serviceRepository;
    protected $expertRepository;

    public function __construct(
        ServiceRepository $serviceRepository,
        ExpertRepository  $expertRepository,
    ){
        $this->serviceRepository = $serviceRepository;
        $this->expertRepository = $expertRepository;
    }

    public function index(FilterRequest $request)
    {
        try {
            $data = $request->validated();
            $filter = app()->make(Filter::class, ['queryParams' => $data]);
            $services = $this->serviceRepository->getAllServices($filter);
            return ServiceResource::collection($services);
        } catch (\Exception $e) {
            \Log::error('Services error: ' . $e->getMessage());
            return response()->json([
                'message' => 'Ошибка при получении данных об услугах.'
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function getExpertServices(int $expertId)
    {
        try {
            $services = $this->serviceRepository->getExpertServices($expertId);
            return response()->json($services);
        } catch (\Exception $e) {
            \Log::error('Services error: ' . $e->getMessage());
            return response()->json([
                'message' => 'Ошибка при получении данных об услугах эксперта.'
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function store(Request $request, StoreServiceRequest $storeRequest)
    {
        try {
            $data = $storeRequest->validated();

            $expertId = $request->input('expert_id');
            if (!$expertId) {
                return response()->json(['message' => 'Поле c id эксперта обязательно!'], Response::HTTP_BAD_REQUEST);
            }
            $data['expert_id'] = $expertId;

            DB::beginTransaction();
            $service = $this->serviceRepository->create($data);

            $expertCategory = ExpertCategory::where('expert_id', $expertId)
                ->where('category_id', $service->category_id)
                ->first();
            if (!$expertCategory) {
                $data = [
                    'expert_id' => $expertId,
                    'category_id' => $service->category_id
                ];
                ExpertCategory::create($data);
            }

            DB::commit();

            \Log::info('Service added successfully', ['service' => $service->toArray()]);

            return response()->json([
                'message' => 'Услуга успешно создана.',
                'service' => $service
            ], Response::HTTP_CREATED);
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Service create error', [
                'error' => $e->getMessage(),
                'data' => $data
            ]);
            return response()->json([
                'message' => 'Ошибка при создании услуги.'
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function updateService(UpdateServiceRequest $updateRequest, int $serviceId){
        $service = $this->serviceRepository->getServiceById($serviceId);
        if (!$service) {
            return response()->json(['message' => 'Услуга не найдена.',], Response::HTTP_NOT_FOUND);
        }

        $this->authorize('update', $service);

        $data = $updateRequest->validated();

        DB::beginTransaction();

        try {
            $service = $this->serviceRepository->update($data, $serviceId);
            if (!$service) {
                throw new \Exception('Ошибка обновления данных услуги.');
            }

            $expertCategory = ExpertCategory::where('expert_id', $service->expert_id)
                ->where('category_id', $service->category_id)
                ->first();
            if (!$expertCategory) {
                $data = [
                    'expert_id' => $service->expert_id,
                    'category_id' => $service->category_id
                ];
                ExpertCategory::create($data);
            }

            DB::commit();

            \Log::info('Данные услуги были успешно обновлены.');

            return response()->json([
                'message' => 'Данные услуги были успешно обновлены.',
                'service' => $service
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Service update error', [
                'service_id' => $serviceId,
                'error' => $e->getMessage()
            ]);
            return response()->json([
                'message' => 'Не получилось обновить данные услуги.'
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function deleteService(int $serviceId)
    {
        $service = $this->serviceRepository->getServiceById($serviceId);
        if (!$service) {
            return response()->json(['message' => 'Услуга не найдена.',], Response::HTTP_NOT_FOUND);
        }

        $this->authorize('delete', $service);

        DB::beginTransaction();
        try {
            if(!$this->serviceRepository->delete($serviceId)){
                throw new \Exception('Ошибка при удалении данных услуги.');
            }
            DB::commit();

            \Log::info('Service deleted successfully');
            return response()->json([
                'message' => 'Услуга была успешно удалена.'
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Service delete error', ['error' => $e->getMessage()]);
            return response()->json([
                'message' => 'Не удалось удалить услугу.'
            ]);
        }
    }
}
