<?php

namespace App\Domain\Sales\Controllers;

use App\Domain\Sales\DTOs\SaleData;
use App\Domain\Sales\Models\Sale;
use App\Domain\Sales\Requests\SaleRequest;
use App\Domain\Sales\Resources\SaleResource;
use App\Domain\Sales\Services\SaleService;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SaleController extends Controller
{
    public function __construct(private readonly SaleService $service)
    {
        $this->authorizeResource(Sale::class, 'sale');
    }

    public function index(Request $request)
    {
        $this->authorize('viewAny', Sale::class);

        $sales = $this->service->list(
            filters: $request->only(['from', 'to']),
            perPage: $request->integer('per_page', 15)
        );

        return SaleResource::collection($sales);
    }

    public function store(SaleRequest $request)
    {
        $sale = $this->service->create(SaleData::fromRequest($request));

        return SaleResource::make($sale)
            ->response()
            ->setStatusCode(Response::HTTP_CREATED);
    }

    public function show(Sale $sale)
    {
        return SaleResource::make($sale->load(['client', 'items', 'payments']));
    }
}
