<?php

namespace App\Domain\Inventory\Repositories;

use App\Domain\Inventory\DTOs\ProductData;
use App\Domain\Inventory\Models\Product;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;

class ProductRepository
{
    public function paginate(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        return Product::query()
            ->with('supplier')
            ->when($filters['search'] ?? null, function (Builder $query, string $search): void {
                $query->where('name', 'like', "%{$search}%");
            })
            ->orderBy('name')
            ->paginate($perPage);
    }

    public function create(ProductData $data): Product
    {
        return Product::create($data->toArray());
    }

    public function update(Product $product, ProductData $data): Product
    {
        $product->update($data->toArray());

        return $product;
    }

    public function delete(Product $product): void
    {
        $product->delete();
    }
}
