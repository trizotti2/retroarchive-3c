<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProductIndexRequest;
use App\Http\Resources\ProductResource;
use App\Services\ProductService;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class ProductController extends Controller
{
    public function __construct(private ProductService $products) {}

    public function index(ProductIndexRequest $request): AnonymousResourceCollection
    {
        return ProductResource::collection($this->products->paginateCatalog($request->validated()));
    }

    public function show(string $id): ProductResource
    {
        return new ProductResource($this->products->findActive($this->positiveId($id)));
    }
}
