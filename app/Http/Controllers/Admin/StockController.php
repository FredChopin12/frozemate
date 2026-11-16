<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\Request;

class StockController extends Controller
{
    public function index(Request $request)
    {
        $filter = $request->input('tampil', 'semua');

        $products = Product::with('category')
            ->when($request->string('q')->trim()->value(), fn ($query, $q) => $query->where('name', 'like', "%$q%"))
            ->when($filter === 'menipis', fn ($query) => $query->lowStock()->where('stock', '>', 0))
            ->when($filter === 'habis', fn ($query) => $query->where('stock', 0))
            ->orderBy('stock')
            ->orderBy('name')
            ->get();

        return view('admin.stock.index', [
            'products' => $products,
            'filter' => $filter,
            'counts' => [
                'semua' => Product::count(),
                'menipis' => Product::lowStock()->where('stock', '>', 0)->count(),
                'habis' => Product::where('stock', 0)->count(),
            ],
            'units' => Product::sum('stock'),
            'value' => Product::get(['price', 'stock'])->sum(fn ($p) => $p->price * $p->stock),
        ]);
    }

    /** Set stok ke angka baru, atau tambah/kurangi lewat "adjust". */
    public function update(Request $request, Product $product)
    {
        $data = $request->validate([
            'stock' => ['required_without:adjust', 'nullable', 'integer', 'min:0', 'max:100000'],
            'adjust' => ['required_without:stock', 'nullable', 'integer', 'between:-1000,1000'],
        ], [], ['stock' => 'stok', 'adjust' => 'penyesuaian']);

        $new = isset($data['adjust'])
            ? max(0, $product->stock + $data['adjust'])
            : $data['stock'];

        $product->update(['stock' => $new]);

        return back()->with('success', "Stok {$product->name} sekarang {$new}.");
    }
}
