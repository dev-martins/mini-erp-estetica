<?php

namespace App\Domain\Inventory\Services;

use App\Domain\Inventory\DTOs\ProductData;
use App\Domain\Inventory\Models\Product;
use App\Domain\Inventory\Repositories\ProductRepository;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class ProductService
{
    public function __construct(private readonly ProductRepository $repository)
    {
    }

    public function list(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        return $this->repository->paginate($filters, $perPage);
    }

    public function create(ProductData $data): Product
    {
        return $this->repository->create($data);
    }

    public function update(Product $product, ProductData $data): Product
    {
        return $this->repository->update($product, $data);
    }

    public function delete(Product $product): void
    {
        $this->repository->delete($product);
    }
}
