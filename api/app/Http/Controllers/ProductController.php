<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProductIndexRequest;
use App\Http\Resources\ProductResource;
use App\Models\Product;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class ProductController extends Controller
{
    public function index(ProductIndexRequest $request): AnonymousResourceCollection
    {
        $filters = $request->validated();
        $query = Product::query()->active()->with('category');

        if (isset($filters['search'])) {
            $query->whereLike('name', '%'.$filters['search'].'%');
        }

        if (isset($filters['category'])) {
            $query->whereHas('category', fn (Builder $category) => $category->where('slug', $filters['category']));
        }

        match ($filters['sort'] ?? 'latest') {
            'price_asc' => $query->orderBy('price_cents'),
            'price_desc' => $query->orderByDesc('price_cents'),
            default => $query->latest(),
        };

        return ProductResource::collection(
            $query->orderByDesc('id')->paginate($filters['per_page'] ?? 12)->withQueryString(),
        );
    }

    public function show(string $id): ProductResource
    {
        $product = Product::query()->active()->with('category')->findOrFail($this->positiveId($id));

        return new ProductResource($product);
    }
}
