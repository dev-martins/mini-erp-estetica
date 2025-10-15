<?php

namespace App\Domain\Services\Controllers;

use App\Domain\Services\DTOs\ServiceData;
use App\Domain\Services\Models\Service;
use App\Domain\Services\Requests\ServiceRequest;
use App\Domain\Services\Resources\ServiceResource;
use App\Domain\Services\Services\ServiceService;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ServiceController extends Controller
{
    public function __construct(private readonly ServiceService $service)
    {
        $this->authorizeResource(Service::class, 'service');
    }

    public function index(Request $request)
    {
        $this->authorize('viewAny', Service::class);

        $services = $this->service->list(
            filters: $request->only(['category', 'active']),
            perPage: $request->integer('per_page', 15)
        );

        return ServiceResource::collection($services);
    }

    public function store(ServiceRequest $request)
    {
        $service = $this->service->create(ServiceData::fromRequest($request));

        return ServiceResource::make($service)
            ->response()
            ->setStatusCode(Response::HTTP_CREATED);
    }

    public function show(Service $service)
    {
        return ServiceResource::make($service);
    }

    public function update(ServiceRequest $request, Service $service)
    {
        $updated = $this->service->update($service, ServiceData::fromRequest($request));

        return ServiceResource::make($updated);
    }

    public function destroy(Service $service)
    {
        $this->service->delete($service);

        return response()->noContent();
    }
}
