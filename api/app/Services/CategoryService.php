<?php

namespace App\Services;

use App\Models\Category;
use Illuminate\Database\QueryException;

class CategoryService
{
    /**
     * @param  array{name: string, slug: string}  $data
     */
    public function create(array $data): Category
    {
        return Category::create($data);
    }

    /**
     * @param  array{name: string, slug: string}  $data
     */
    public function update(Category $category, array $data): Category
    {
        $category->update($data);

        return $category;
    }

    public function delete(Category $category): void
    {
        $message = 'A categoria contém produtos vinculados.';
        abort_if($category->products()->withTrashed()->exists(), 409, $message);

        try {
            $category->delete();
        } catch (QueryException $exception) {
            if (in_array($exception->getCode(), ['23000', '23503'], true)) {
                abort(409, $message);
            }

            throw $exception;
        }
    }
}
