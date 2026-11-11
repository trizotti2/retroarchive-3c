<?php

namespace App\Services;

use App\Models\Product;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Storage;
use Throwable;

class ProductService
{
    /**
     * @param  array{search?: string, category?: string, sort?: string, page?: int|string, per_page?: int|string}  $filters
     */
    public function paginateCatalog(array $filters): LengthAwarePaginator
    {
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

        return $query->orderByDesc('id')->paginate($filters['per_page'] ?? 12)->withQueryString();
    }

    public function findActive(int $productId): Product
    {
        return Product::query()->active()->with('category')->findOrFail($productId);
    }

    public function paginateForAdmin(): LengthAwarePaginator
    {
        return Product::query()->with('category')->latest('id')->paginate(20);
    }

    /**
     * @param  array{name: string, description: string, category_id: int|string, price_cents: int|string, stock: int|string, is_active: bool|int|string, photo_url?: string|null}  $data
     */
    public function save(Product $product, array $data, ?UploadedFile $image = null): Product
    {
        $fields = Arr::except($data, ['photo_url']);
        $externalUrl = $data['photo_url'] ?? null;
        $previous = $product->image_path;
        $path = null;

        if ($image !== null) {
            $path = $image->store('products', 'public');
            abort_if($path === false, 500, 'Não foi possível gravar a imagem.');
            $fields['image_path'] = $path;
            $fields['photo_url'] = Product::storagePhotoUrl($path);
        } elseif ($externalUrl !== null) {
            $fields['image_path'] = null;
            $fields['photo_url'] = $externalUrl;
        } elseif (array_key_exists('photo_url', $data) && $product->image_path === null) {
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

    public function delete(Product $product): void
    {
        $product->delete();
    }
}
