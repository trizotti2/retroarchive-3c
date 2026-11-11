<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $photoUrl = $this->resource->photoUrl();

        return [
            'id' => $this->id,
            'name' => $this->name,
            'description' => $this->description,
            'price_cents' => $this->price_cents,
            'stock' => $this->stock,
            'is_active' => $this->is_active,
            'available' => $this->is_active && $this->stock > 0,
            'photo_url' => $photoUrl,
            'image_url' => $photoUrl,
            'category' => $this->whenLoaded('category', fn () => new CategoryResource($this->category)),
        ];
    }
}
