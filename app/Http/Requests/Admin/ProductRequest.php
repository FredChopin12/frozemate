<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ProductRequest extends FormRequest
{
    public function authorize(): bool
    {
        return (bool) $this->user()?->isAdmin();
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'is_active' => $this->boolean('is_active'),
            'is_featured' => $this->boolean('is_featured'),
            'tags' => $this->input('tags', []),
        ]);
    }

    public function rules(): array
    {
        return [
            'category_id' => ['required', 'integer', 'exists:categories,id'],
            'name' => ['required', 'string', 'min:3', 'max:120'],
            'description' => ['nullable', 'string', 'max:1000'],
            'price' => ['required', 'integer', 'min:500', 'max:10000000'],
            'stock' => ['required', 'integer', 'min:0', 'max:100000'],
            'weight_grams' => ['required', 'integer', 'min:10', 'max:20000'],
            'servings' => ['required', 'integer', 'min:1', 'max:100'],
            'cook_method' => ['nullable', 'string', 'max:60'],
            'tags' => ['array'],
            'tags.*' => [Rule::in(array_keys(config('frozemate.needs')))],
            'image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'remove_image' => ['nullable', 'boolean'],
            'is_active' => ['boolean'],
            'is_featured' => ['boolean'],
        ];
    }

    public function attributes(): array
    {
        return [
            'category_id' => 'kategori',
            'name' => 'nama produk',
            'description' => 'deskripsi',
            'price' => 'harga',
            'stock' => 'stok',
            'weight_grams' => 'berat',
            'servings' => 'jumlah porsi',
            'cook_method' => 'cara masak',
            'image' => 'foto produk',
        ];
    }
}
