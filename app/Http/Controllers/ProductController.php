<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        $filters = $request->validate([
            'q' => ['nullable', 'string', 'max:60'],
            'kategori' => ['nullable', 'string', 'max:80'],
            'kebutuhan' => ['nullable', 'in:'.implode(',', array_keys(config('frozemate.needs')))],
            'urut' => ['nullable', 'in:terbaru,termurah,termahal,hemat'],
            'tersedia' => ['nullable', 'boolean'],
        ]);

        $products = Product::with('category')->active()
            ->when($filters['q'] ?? null, function ($query, $q) {
                $query->where(fn ($w) => $w
                    ->where('name', 'like', "%$q%")
                    ->orWhere('description', 'like', "%$q%"));
            })
            ->when($filters['kategori'] ?? null, fn ($query, $slug) => $query
                ->whereHas('category', fn ($c) => $c->where('slug', $slug)))
            ->when($filters['kebutuhan'] ?? null, fn ($query, $need) => $query
                ->whereJsonContains('tags', $need))
            ->when($filters['tersedia'] ?? null, fn ($query) => $query->where('stock', '>', 0))
            ->when($filters['urut'] ?? 'terbaru', fn ($query, $sort) => match ($sort) {
                'termurah' => $query->orderBy('price'),
                'termahal' => $query->orderByDesc('price'),
                'hemat' => $query->orderByRaw('price / servings'),
                default => $query->orderByDesc('is_featured')->latest('id'),
            })
            ->paginate(12)
            ->withQueryString();

        return view('products.index', [
            'products' => $products,
            'categories' => Category::withCount(['products' => fn ($q) => $q->active()])->orderBy('name')->get(),
            'filters' => $filters,
            'activeCategory' => isset($filters['kategori'])
                ? Category::where('slug', $filters['kategori'])->first()
                : null,
        ]);
    }

    public function show(Product $product)
    {
        abort_unless($product->is_active, 404);

        $product->load('category');

        return view('products.show', [
            'product' => $product,
            'related' => Product::with('category')->active()
                ->where('category_id', $product->category_id)
                ->whereKeyNot($product->id)
                ->take(4)->get(),
        ]);
    }
}
