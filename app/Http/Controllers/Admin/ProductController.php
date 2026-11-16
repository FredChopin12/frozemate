<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ProductRequest;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        $products = Product::with('category')
            ->when($request->string('q')->trim()->value(), fn ($query, $q) => $query->where('name', 'like', "%$q%"))
            ->when($request->integer('kategori'), fn ($query, $id) => $query->where('category_id', $id))
            ->when($request->input('status'), fn ($query, $status) => match ($status) {
                'aktif' => $query->where('is_active', true),
                'nonaktif' => $query->where('is_active', false),
                'menipis' => $query->lowStock(),
                default => $query,
            })
            ->orderBy('name')
            ->paginate(12)
            ->withQueryString();

        return view('admin.products.index', [
            'products' => $products,
            'categories' => Category::orderBy('name')->get(),
        ]);
    }

    public function create()
    {
        return view('admin.products.form', [
            'product' => new Product(['is_active' => true, 'servings' => 3, 'weight_grams' => 250, 'tags' => []]),
            'categories' => Category::orderBy('name')->get(),
        ]);
    }

    public function store(ProductRequest $request)
    {
        $data = $this->payload($request);
        $data['slug'] = $this->uniqueSlug($data['name']);

        if ($request->hasFile('image')) {
            $data['image'] = $request->file('image')->store('products', 'uploads');
        }

        $product = Product::create($data);

        return redirect()->route('admin.products.index')
            ->with('success', "{$product->name} ditambahkan ke katalog.");
    }

    public function edit(Product $product)
    {
        return view('admin.products.form', [
            'product' => $product,
            'categories' => Category::orderBy('name')->get(),
        ]);
    }

    public function update(ProductRequest $request, Product $product)
    {
        $data = $this->payload($request);

        if ($data['name'] !== $product->name) {
            $data['slug'] = $this->uniqueSlug($data['name'], $product->id);
        }

        if ($request->hasFile('image')) {
            $this->deleteImage($product);
            $data['image'] = $request->file('image')->store('products', 'uploads');
        } elseif ($request->boolean('remove_image')) {
            $this->deleteImage($product);
            $data['image'] = null;
        }

        $product->update($data);

        return redirect()->route('admin.products.index')
            ->with('success', "{$product->name} diperbarui.");
    }

    public function destroy(Product $product)
    {
        $this->deleteImage($product);
        $product->delete(); // riwayat pesanan tetap aman: nama & harga sudah disalin ke order_items

        return redirect()->route('admin.products.index')
            ->with('info', "{$product->name} dihapus dari katalog.");
    }

    private function payload(ProductRequest $request): array
    {
        return collect($request->validated())->except(['image', 'remove_image'])->all();
    }

    private function uniqueSlug(string $name, ?int $ignoreId = null): string
    {
        $base = Str::slug($name) ?: 'produk';
        $slug = $base;
        $i = 2;

        while (Product::where('slug', $slug)->when($ignoreId, fn ($q) => $q->whereKeyNot($ignoreId))->exists()) {
            $slug = $base.'-'.$i++;
        }

        return $slug;
    }

    private function deleteImage(Product $product): void
    {
        if ($product->image) {
            Storage::disk('uploads')->delete($product->image);
        }
    }
}
