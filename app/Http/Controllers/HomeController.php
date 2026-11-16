<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use App\Services\BundleRecommender;

class HomeController extends Controller
{
    public function index(BundleRecommender $recommender)
    {
        // Contoh nyata di beranda: dihitung dari katalog & stok saat ini.
        $exampleParams = ['need' => 'kos', 'people' => 1, 'budget' => 'b2', 'meals' => 5];

        return view('home', [
            'featured' => Product::with('category')->active()
                ->orderByDesc('is_featured')->orderByDesc('stock')
                ->take(8)->get(),
            'categories' => Category::withCount(['products' => fn ($q) => $q->active()])
                ->orderBy('name')->get(),
            'productCount' => Product::active()->count(),
            'lowestPrice' => (int) Product::available()->min('price'),
            'exampleParams' => $exampleParams,
            'example' => $recommender->recommend(...$exampleParams)->first(),
        ]);
    }
}
