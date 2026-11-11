<?php

namespace App\Http\Controllers;

use App\Http\Requests\SaveCategoryRequest;
use App\Http\Resources\CategoryResource;
use App\Models\Category;
use Illuminate\Database\QueryException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;

class AdminCategoryController extends Controller
{
    public function store(SaveCategoryRequest $request): JsonResponse
    {
        $category = Category::create($request->validated());

        return (new CategoryResource($category))->response()->setStatusCode(201);
    }

    public function update(SaveCategoryRequest $request, Category $category): CategoryResource
    {
        $category->update($request->validated());

        return new CategoryResource($category);
    }

    public function destroy(Category $category): Response
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

        return response()->noContent();
    }
}
