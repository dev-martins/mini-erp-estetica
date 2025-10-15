<?php

namespace App\Domain\Inventory\Controllers;

use App\Domain\Inventory\DTOs\StockMovementData;
use App\Domain\Inventory\Models\StockMovement;
use App\Domain\Inventory\Requests\StockMovementRequest;
use App\Domain\Inventory\Resources\StockMovementResource;
use App\Domain\Inventory\Services\StockMovementService;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class StockMovementController extends Controller
{
    public function __construct(private readonly StockMovementService $service)
    {
    }

    public function index(Request $request)
    {
        $this->authorize('viewAny', StockMovement::class);

        $movements = $this->service->list(
            filters: $request->only(['product_id']),
            perPage: $request->integer('per_page', 20)
        );

        return StockMovementResource::collection($movements);
    }

    public function store(StockMovementRequest $request)
    {
        $this->authorize('create', StockMovement::class);

        $this->service->register(StockMovementData::fromRequest($request));

        return response()->json([
            'message' => 'Stock movement registered.',
        ], Response::HTTP_CREATED);
    }
}
