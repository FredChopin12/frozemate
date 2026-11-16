<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class CategoryController extends Controller
{
    public function index()
    {
        return view('admin.categories.index', [
            'categories' => Category::withCount('products')->orderBy('name')->get(),
            'category' => new Category(['tone' => 'blue', 'art' => 'box']),
        ]);
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);
        $data['slug'] = $this->uniqueSlug($data['name']);

        $category = Category::create($data);

        return redirect()->route('admin.categories.index')
            ->with('success', "Kategori {$category->name} ditambahkan.");
    }

    public function edit(Category $category)
    {
        return view('admin.categories.edit', ['category' => $category]);
    }

    public function update(Request $request, Category $category)
    {
        $data = $this->validated($request, $category);

        if ($data['name'] !== $category->name) {
            $data['slug'] = $this->uniqueSlug($data['name'], $category->id);
        }

        $category->update($data);

        return redirect()->route('admin.categories.index')
            ->with('success', "Kategori {$category->name} diperbarui.");
    }

    public function destroy(Category $category)
    {
        if ($category->products()->exists()) {
            return back()->with('error', "Kategori {$category->name} masih berisi produk. Pindahkan atau hapus produknya dulu.");
        }

        $category->delete();

        return back()->with('info', "Kategori {$category->name} dihapus.");
    }

    private function validated(Request $request, ?Category $category = null): array
    {
        $request->merge(['is_side' => $request->boolean('is_side')]);

        return $request->validate([
            'name' => ['required', 'string', 'min:2', 'max:60', Rule::unique('categories', 'name')->ignore($category?->id)],
            'description' => ['nullable', 'string', 'max:160'],
            'tone' => ['required', Rule::in(array_keys(config('frozemate.tones')))],
            'art' => ['required', Rule::in(array_keys(config('frozemate.arts')))],
            'is_side' => ['boolean'],
        ], [], [
            'name' => 'nama kategori',
            'description' => 'deskripsi',
            'tone' => 'warna kemasan',
            'art' => 'ilustrasi',
        ]);
    }

    private function uniqueSlug(string $name, ?int $ignoreId = null): string
    {
        $base = Str::slug($name) ?: 'kategori';
        $slug = $base;
        $i = 2;

        while (Category::where('slug', $slug)->when($ignoreId, fn ($q) => $q->whereKeyNot($ignoreId))->exists()) {
            $slug = $base.'-'.$i++;
        }

        return $slug;
    }
}
