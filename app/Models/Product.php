<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Product extends Model
{
    protected $fillable = [
        'category_id', 'name', 'slug', 'description', 'price', 'stock',
        'weight_grams', 'servings', 'cook_method', 'tags', 'image',
        'is_active', 'is_featured',
    ];

    protected function casts(): array
    {
        return [
            'tags' => 'array',
            'is_active' => 'boolean',
            'is_featured' => 'boolean',
            'price' => 'integer',
            'stock' => 'integer',
            'servings' => 'integer',
            'weight_grams' => 'integer',
        ];
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function orderItems(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    /** Produk yang tampil di toko. */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    /** Produk yang bisa dibeli sekarang. */
    public function scopeAvailable(Builder $query): Builder
    {
        return $query->where('is_active', true)->where('stock', '>', 0);
    }

    public function scopeLowStock(Builder $query): Builder
    {
        return $query->where('stock', '<=', config('frozemate.low_stock'));
    }

    public function isLowStock(): bool
    {
        return $this->stock > 0 && $this->stock <= config('frozemate.low_stock');
    }

    public function isSoldOut(): bool
    {
        return $this->stock <= 0;
    }

    public function weightLabel(): string
    {
        return $this->weight_grams >= 1000
            ? rtrim(rtrim(number_format($this->weight_grams / 1000, 1, ',', '.'), '0'), ',').' kg'
            : $this->weight_grams.' g';
    }

    /**
     * Porsi yang dihitung Smart Bundle. Pendamping/camilan dihitung setengah
     * karena tidak mengenyangkan sendirian — kecuali untuk kebutuhan "acara".
     */
    public function effectiveServings(?string $need = null): float
    {
        return ($this->category?->is_side && $need !== 'acara')
            ? $this->servings * 0.5
            : (float) $this->servings;
    }

    public function pricePerServing(): int
    {
        return (int) round($this->price / max(1, $this->servings));
    }

    public function imageUrl(): ?string
    {
        return $this->image ? asset('uploads/'.$this->image) : null;
    }

    public function tone(): string
    {
        return $this->category?->tone ?? 'blue';
    }

    public function art(): string
    {
        return $this->category?->art ?? 'box';
    }
}
