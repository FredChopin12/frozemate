@extends('layouts.admin')

@php
    $editing = $product->exists;
    $selectedTags = old('tags', $product->tags ?? []);
    $selectedCategory = $categories->firstWhere('id', (int) old('category_id', $product->category_id)) ?? $categories->first();
@endphp

@section('title', $editing ? 'Edit '.$product->name : 'Produk baru')
@section('eyebrow', 'Katalog / Produk')
@section('heading', $editing ? 'Edit produk' : 'Produk baru')

@section('actions')
    <a class="btn-fm btn-fm--ghost" href="{{ route('admin.products.index') }}"><i class="ph-bold ph-arrow-left"></i> Kembali</a>
@endsection

@section('content')
    @if ($categories->isEmpty())
        <div class="notice mb-3"><i class="ph-fill ph-warning"></i><div>Belum ada kategori. <a href="{{ route('admin.categories.index') }}">Buat kategori dulu</a> sebelum menambah produk.</div></div>
    @endif

    <form method="POST" enctype="multipart/form-data" novalidate
          action="{{ $editing ? route('admin.products.update', $product) : route('admin.products.store') }}">
        @csrf
        @if ($editing) @method('PUT') @endif

        <div class="row g-3">
            <div class="col-xl-8">
                <div class="card-fm mb-3">
                    <div class="card-fm__head"><h3>Informasi produk</h3></div>
                    <div class="card-fm__body">
                        <div class="row g-3">
                            <div class="col-md-8">
                                <label class="form-label" for="name">Nama produk</label>
                                <input type="text" id="name" name="name" value="{{ old('name', $product->name) }}" required
                                       class="form-control @error('name') is-invalid @enderror" placeholder="Contoh: Nugget Ayam Original">
                                @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-md-4">
                                <label class="form-label" for="category_id">Kategori</label>
                                <select id="category_id" name="category_id" class="form-select @error('category_id') is-invalid @enderror" required>
                                    @foreach ($categories as $category)
                                        <option value="{{ $category->id }}" @selected($selectedCategory?->id === $category->id)>{{ $category->name }}</option>
                                    @endforeach
                                </select>
                                @error('category_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-12">
                                <label class="form-label" for="description">Deskripsi</label>
                                <textarea id="description" name="description" rows="3" class="form-control @error('description') is-invalid @enderror"
                                          placeholder="Ceritakan isi, rasa, dan cara menikmatinya.">{{ old('description', $product->description) }}</textarea>
                                @error('description') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card-fm mb-3">
                    <div class="card-fm__head"><h3>Harga, stok &amp; kemasan</h3></div>
                    <div class="card-fm__body">
                        <div class="row g-3">
                            <div class="col-sm-6 col-md-3">
                                <label class="form-label" for="price">Harga</label>
                                <div class="input-affix">
                                    <span class="affix">Rp</span>
                                    <input type="number" id="price" name="price" min="500" step="500" inputmode="numeric" value="{{ old('price', $product->price) }}" required
                                           class="form-control @error('price') is-invalid @enderror">
                                </div>
                                @error('price') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-sm-6 col-md-3">
                                <label class="form-label" for="stock">Stok</label>
                                <div class="input-affix input-affix--end">
                                    <input type="number" id="stock" name="stock" min="0" inputmode="numeric" value="{{ old('stock', $product->stock ?? 0) }}" required
                                           class="form-control @error('stock') is-invalid @enderror">
                                    <span class="affix">pack</span>
                                </div>
                                @error('stock') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-sm-6 col-md-3">
                                <label class="form-label" for="weight_grams">Berat</label>
                                <div class="input-affix input-affix--end">
                                    <input type="number" id="weight_grams" name="weight_grams" min="10" step="10" inputmode="numeric" value="{{ old('weight_grams', $product->weight_grams) }}" required
                                           class="form-control @error('weight_grams') is-invalid @enderror">
                                    <span class="affix">gram</span>
                                </div>
                                @error('weight_grams') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-sm-6 col-md-3">
                                <label class="form-label" for="servings">Isi</label>
                                <div class="input-affix input-affix--end">
                                    <input type="number" id="servings" name="servings" min="1" inputmode="numeric" value="{{ old('servings', $product->servings) }}" required
                                           class="form-control @error('servings') is-invalid @enderror">
                                    <span class="affix">porsi</span>
                                </div>
                                @error('servings') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label" for="cook_method">Cara masak <span class="muted fw-normal">(opsional)</span></label>
                                <input type="text" id="cook_method" name="cook_method" value="{{ old('cook_method', $product->cook_method) }}"
                                       class="form-control @error('cook_method') is-invalid @enderror" placeholder="Goreng 3 menit">
                                @error('cook_method') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                        </div>
                        <p class="form-text mt-3 mb-0"><i class="ph ph-info"></i> Jumlah porsi dipakai Smart Bundle untuk menghitung apakah bundel cukup untuk kebutuhan customer.</p>
                    </div>
                </div>

                <div class="card-fm">
                    <div class="card-fm__head"><h3>Cocok untuk kebutuhan</h3><span class="tag">Smart Bundle</span></div>
                    <div class="card-fm__body">
                        <div class="swatch-pick art-pick">
                            @foreach (config('frozemate.needs') as $key => $need)
                                <input type="checkbox" name="tags[]" id="tag-{{ $key }}" value="{{ $key }}" @checked(in_array($key, $selectedTags))>
                                <label for="tag-{{ $key }}"><i class="ph {{ $need['icon'] }}"></i> {{ $need['label'] }}</label>
                            @endforeach
                        </div>
                        @error('tags.*') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                        <p class="form-text mt-3 mb-0">Produk yang ditandai akan diutamakan saat customer memilih kebutuhan tersebut.</p>
                    </div>
                </div>
            </div>

            <div class="col-xl-4">
                <div class="card-fm mb-3">
                    <div class="card-fm__head"><h3>Tampilan</h3></div>
                    <div class="card-fm__body">
                        <div class="preview-frame mb-3">
                            <x-pack :product="$product" :tone="$selectedCategory?->tone" :art="$selectedCategory?->art" id="packPreview" />
                        </div>

                        <label class="form-label" for="image">Foto produk <span class="muted fw-normal">(opsional)</span></label>
                        <input type="file" id="image" name="image" accept="image/jpeg,image/png,image/webp" data-image-input="#packPreview"
                               class="form-control @error('image') is-invalid @enderror">
                        @error('image') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        <div class="form-text">JPG, PNG, atau WebP, maks 2 MB. Tanpa foto, toko menampilkan ilustrasi kemasan kategori.</div>

                        @if ($product->image)
                            <div class="form-check mt-2">
                                <input class="form-check-input" type="checkbox" name="remove_image" value="1" id="remove_image">
                                <label class="form-check-label" for="remove_image">Hapus foto saat ini</label>
                            </div>
                        @endif
                    </div>
                </div>

                <div class="card-fm mb-3">
                    <div class="card-fm__head"><h3>Status</h3></div>
                    <div class="card-fm__body d-grid gap-3">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" role="switch" name="is_active" value="1" id="is_active" @checked(old('is_active', $product->is_active))>
                            <label class="form-check-label" for="is_active"><strong>Tampilkan di toko</strong><br><small class="muted">Matikan untuk menyembunyikan tanpa menghapus.</small></label>
                        </div>
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" role="switch" name="is_featured" value="1" id="is_featured" @checked(old('is_featured', $product->is_featured))>
                            <label class="form-check-label" for="is_featured"><strong>Tandai laris</strong><br><small class="muted">Muncul lebih dulu di beranda dan katalog.</small></label>
                        </div>
                    </div>
                </div>

                <button class="btn-fm btn-fm--primary btn-fm--lg btn-fm--block" type="submit" @disabled($categories->isEmpty())>
                    <i class="ph-bold ph-floppy-disk"></i> {{ $editing ? 'Simpan perubahan' : 'Tambah ke katalog' }}
                </button>
            </div>
        </div>
    </form>
@endsection
