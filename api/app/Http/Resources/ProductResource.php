<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'description' => $this->description,
            'price_cents' => $this->price_cents,
            'stock' => $this->stock,
            'available' => $this->stock > 0,
            'image_url' => $this->image_path
                ? '/storage/'.$this->image_path
                : null,
            'category' => $this->whenLoaded(
                'category',
                fn () => new CategoryResource($this->category),
            ),
        ];
    }
}