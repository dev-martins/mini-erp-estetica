<?php

namespace App\Domain\Inventory\Services;

use App\Domain\Inventory\DTOs\StockMovementData;
use App\Domain\Inventory\Models\Product;
use App\Domain\Inventory\Repositories\StockMovementRepository;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class StockMovementService
{
    public function __construct(
        private readonly StockMovementRepository $repository,
        private readonly ProductService $products
    ) {
    }

    public function list(array $filters = [], int $perPage = 20): LengthAwarePaginator
    {
        return $this->repository->paginate($filters, $perPage);
    }

    public function register(StockMovementData $data): void
    {
        DB::transaction(function () use ($data): void {
            $movement = $this->repository->create($data, auth()->id());

            /** @var Product $product */
            $product = $movement->product()->lockForUpdate()->first();
            match ($data->type) {
                'in' => $product->increment('current_stock', $data->qty),
                'out' => $product->decrement('current_stock', $data->qty),
                'adjustment' => $product->update(['current_stock' => $data->qty]),
                default => null,
            };
        });
    }
}
