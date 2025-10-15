<?php

namespace App\Domain\Inventory\Controllers;

use App\Domain\Inventory\DTOs\ProductData;
use App\Domain\Inventory\Models\Product;
use App\Domain\Inventory\Requests\ProductRequest;
use App\Domain\Inventory\Resources\ProductResource;
use App\Domain\Inventory\Services\ProductService;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ProductController extends Controller
{
    public function __construct(private readonly ProductService $service)
    {
        $this->authorizeResource(Product::class, 'product');
    }

    public function index(Request $request)
    {
        $this->authorize('viewAny', Product::class);

        $products = $this->service->list(
            filters: $request->only(['search']),
            perPage: $request->integer('per_page', 15)
        );

        return ProductResource::collection($products);
    }

    public function store(ProductRequest $request)
    {
        $product = $this->service->create(ProductData::fromRequest($request));

        return ProductResource::make($product)
            ->response()
            ->setStatusCode(Response::HTTP_CREATED);
    }

    public function show(Product $product)
    {
        return ProductResource::make($product->load('supplier'));
    }

    public function update(ProductRequest $request, Product $product)
    {
        $updated = $this->service->update($product, ProductData::fromRequest($request));

        return ProductResource::make($updated->load('supplier'));
    }

    public function destroy(Product $product)
    {
        $this->service->delete($product);

        return response()->noContent();
    }
}
