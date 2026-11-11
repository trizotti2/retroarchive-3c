<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Product extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'category_id',
        'name',
        'description',
        'price_cents',
        'stock',
        'image_path',
        'photo_url',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'price_cents' => 'integer',
            'stock' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    #[Scope]
    protected function active(Builder $query): void
    {
        $query->where('is_active', true);
    }

    public static function storagePhotoUrl(string $path): string
    {
        return '/storage/'.ltrim($path, '/');
    }

    public function photoUrl(): ?string
    {
        return $this->photo_url ?: ($this->image_path ? self::storagePhotoUrl($this->image_path) : null);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }
}
