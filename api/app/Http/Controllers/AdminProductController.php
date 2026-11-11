<?php

namespace App\Http\Controllers;

use App\Http\Requests\SaveProductRequest;
use App\Http\Resources\ProductResource;
use App\Models\Product;
use App\Services\ProductService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;

class AdminProductController extends Controller
{
    public function __construct(private ProductService $products) {}

    public function index(): AnonymousResourceCollection
    {
        return ProductResource::collection($this->products->paginateForAdmin());
    }

    public function store(SaveProductRequest $request): JsonResponse
    {
        $product = $this->products->save(new Product, $request->safe()->except(['image']), $request->file('image'));

        return (new ProductResource($product))->response()->setStatusCode(201);
    }

    public function update(SaveProductRequest $request, Product $product): ProductResource
    {
        return new ProductResource(
            $this->products->save($product, $request->safe()->except(['image']), $request->file('image')),
        );
    }

    public function destroy(Product $product): Response
    {
        $this->products->delete($product);

        return response()->noContent();
    }
}
