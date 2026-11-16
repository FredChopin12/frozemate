<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Category extends Model
{
    protected $fillable = ['name', 'slug', 'description', 'tone', 'art', 'is_side'];

    protected function casts(): array
    {
        return [
            // true = pendamping/camilan, bukan lauk utama (dipakai Smart Bundle).
            'is_side' => 'boolean',
        ];
    }

    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }
}
