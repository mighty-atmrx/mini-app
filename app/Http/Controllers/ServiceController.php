<?php

namespace App\Http\Controllers;

use App\Http\Requests\Service\StoreServiceRequest;
use App\Http\Requests\Service\UpdateServiceRequest;
use App\Http\Resources\ServiceResource;
use App\Models\ExpertCategory;
use App\Repositories\ServiceRepository;
use App\Repositories\ExpertRepository;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

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

    public function index()
    {
        try {
            $services = $this->serviceRepository->getAllServices()->paginate(10);
            return ServiceResource::collection($services);
        } catch (\Exception $e) {
            \Log::error('Services error: ' . $e->getMessage());
            return response()->json([
                'message' => 'Something went wrong',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function getExpertServices(int $expertId)
    {
        try {
            $services = $this->serviceRepository->getExpertServices($expertId)->paginate(10);
            return response()->json($services);
        } catch (\Exception $e) {
            \Log::error('Services error: ' . $e->getMessage());
            return response()->json([
                'message' => 'Something went wrong',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function store(Request $request, StoreServiceRequest $storeRequest)
    {
        try {
            $data = $storeRequest->validated();

            $expertId = $request->input('expert_id');
            if (!$expertId) {
                return response()->json(['message' => 'Expert ID is required'], 400);
            }
            $data['expert_id'] = $expertId;

            DB::beginTransaction();
            $service = $this->serviceRepository->create($data);
            if (!$service) {
                throw new \Exception('Failed to create service');
            }

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
                'message' => 'Service added successfully',
                'service' => $service
            ], 201);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'message' => 'Validation error',
                'errors' => $e->getMessage()
            ], 422);
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Service create error', [
                'error' => $e->getMessage(),
                'data' => $data
            ]);
            return response()->json([
                'message' => 'Service not created',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function updateService(UpdateServiceRequest $updateRequest, int $serviceId){
        $service = $this->serviceRepository->getServiceById($serviceId);
        if (!$service) {
            return response()->json(['message' => 'Service not found',], 404);
        }

        $this->authorize('update', $service);

        $data = $updateRequest->validated();

        DB::beginTransaction();

        try {
            $service = $this->serviceRepository->update($data, $serviceId);
            if (!$service) {
                throw new \Exception('Failed to update service');
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

            \Log::info('Service updated successfully');

            return response()->json([
                'message' => 'Service updated successfully',
                'service' => $service
            ]);
        } catch (\Exception $e) {
            \Log::error('Service update error', [
                'service_id' => $serviceId,
                'error' => $e->getMessage()
            ]);
            return response()->json([
                'message' => 'Service not updated',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function deleteService(int $serviceId)
    {
        $service = $this->serviceRepository->getServiceById($serviceId);
        if (!$service) {
            return response()->json(['message' => 'Service not found',], 404);
        }

        $this->authorize('delete', $service);

        DB::beginTransaction();
        try {
            $this->serviceRepository->delete($serviceId);
            DB::commit();

            \Log::info('Service deleted successfully');
            return response()->json([
                'message' => 'Service deleted successfully'
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Service delete error', ['error' => $e->getMessage()]);
            return response()->json([
                'message' => 'Service not deleted',
                'error' => $e->getMessage()
            ]);
        }
    }
}
