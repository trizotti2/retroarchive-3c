<?php

namespace App\Http\Controllers;

use App\Http\Requests\SaveProductRequest;
use App\Http\Resources\ProductResource;
use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Storage;
use Throwable;

class AdminProductController extends Controller
{
    public function index(): AnonymousResourceCollection
    {
        return ProductResource::collection(Product::query()->with('category')->latest('id')->paginate(20));
    }

    public function store(SaveProductRequest $request): JsonResponse
    {
        $product = $this->save($request, new Product());

        return (new ProductResource($product))->response()->setStatusCode(201);
    }

    public function update(SaveProductRequest $request, Product $product): ProductResource
    {
        return new ProductResource($this->save($request, $product));
    }

    public function destroy(Product $product): Response
    {
        $product->delete();

        return response()->noContent();
    }

    private function save(SaveProductRequest $request, Product $product): Product
    {
        $fields = $request->safe()->except(['image', 'photo_url']);
        $externalUrl = $request->validated('photo_url');
        $previous = $product->image_path;
        $path = null;

        if ($request->hasFile('image')) {
            $path = $request->file('image')->store('products', 'public');
            abort_if($path === false, 500, 'Não foi possível gravar a imagem.');
            $fields['image_path'] = $path;
            $fields['photo_url'] = Product::storagePhotoUrl($path);
        } elseif ($externalUrl !== null) {
            $fields['image_path'] = null;
            $fields['photo_url'] = $externalUrl;
        } elseif ($request->has('photo_url') && $product->image_path === null) {
            $fields['photo_url'] = null;
        }

        try {
            $product->fill($fields)->save();
        } catch (Throwable $exception) {
            if ($path !== null) {
                Storage::disk('public')->delete($path);
            }

            throw $exception;
        }

        if (($path !== null || $externalUrl !== null) && $previous && str_starts_with($previous, 'products/')) {
            Storage::disk('public')->delete($previous);
        }

        return $product->load('category');
    }
}
