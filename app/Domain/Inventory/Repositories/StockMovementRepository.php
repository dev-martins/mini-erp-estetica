<?php

namespace App\Domain\Inventory\Repositories;

use App\Domain\Inventory\DTOs\StockMovementData;
use App\Domain\Inventory\Models\StockMovement;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class StockMovementRepository
{
    public function paginate(array $filters = [], int $perPage = 20): LengthAwarePaginator
    {
        return StockMovement::query()
            ->with(['product', 'batch'])
            ->when($filters['product_id'] ?? null, fn ($query, $productId) => $query->where('product_id', $productId))
            ->orderByDesc('created_at')
            ->paginate($perPage);
    }

    public function create(StockMovementData $data, int $userId = null): StockMovement
    {
        $payload = $data->toArray();
        $payload['created_by'] = $userId;

        return StockMovement::create($payload);
    }
}
