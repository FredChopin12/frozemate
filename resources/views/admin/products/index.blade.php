@extends('layouts.admin')

@section('title', 'Produk')
@section('eyebrow', 'Katalog')
@section('heading', 'Produk')

@section('actions')
    <a class="btn-fm btn-fm--primary" href="{{ route('admin.products.create') }}"><i class="ph-bold ph-plus"></i> Tambah produk</a>
@endsection

@section('content')
    <div class="card-fm">
        <div class="card-fm__head">
            <form class="filter-bar mb-0 flex-grow-1" method="GET">
                <div class="input-affix input-affix--icon" style="flex:1;min-width:200px;max-width:320px">
                    <i class="affix ph ph-magnifying-glass"></i>
                    <input type="search" class="form-control" style="width:100%" name="q" value="{{ request('q') }}" placeholder="Cari nama produk…" aria-label="Cari produk">
                </div>
                <select class="form-select" name="kategori" aria-label="Kategori" onchange="this.form.submit()">
                    <option value="">Semua kategori</option>
                    @foreach ($categories as $category)
                        <option value="{{ $category->id }}" @selected(request()->integer('kategori') === $category->id)>{{ $category->name }}</option>
                    @endforeach
                </select>
                <select class="form-select" name="status" aria-label="Status" onchange="this.form.submit()">
                    <option value="">Semua status</option>
                    <option value="aktif" @selected(request('status') === 'aktif')>Tampil di toko</option>
                    <option value="nonaktif" @selected(request('status') === 'nonaktif')>Disembunyikan</option>
                    <option value="menipis" @selected(request('status') === 'menipis')>Stok menipis</option>
                </select>
                <span class="result-count ms-auto">{{ $products->total() }} produk</span>
            </form>
        </div>

        <div class="table-wrap">
            <table class="table-fm">
                <thead>
                    <tr>
                        <th>Produk</th>
                        <th>Kategori</th>
                        <th class="num">Harga</th>
                        <th class="num">Stok</th>
                        <th>Status</th>
                        <th class="num">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($products as $product)
                        <tr>
                            <td>
                                <div class="cell-product">
                                    <x-pack :product="$product" size="mini" />
                                    <div>
                                        <a class="row-link" href="{{ route('admin.products.edit', $product) }}">{{ $product->name }}</a>
                                        <small>{{ $product->weightLabel() }} · {{ $product->servings }} porsi</small>
                                    </div>
                                </div>
                            </td>
                            <td>{{ $product->category->name }}</td>
                            <td class="num fw-semibold">{{ rupiah($product->price) }}</td>
                            <td class="num">
                                <span @class(['stock-pill', 'stock-pill--out' => $product->isSoldOut(), 'stock-pill--low' => $product->isLowStock()])>{{ $product->stock }}</span>
                            </td>
                            <td><span @class(['dot-state', 'dot-state--off' => ! $product->is_active])>{{ $product->is_active ? 'Tampil' : 'Disembunyikan' }}</span></td>
                            <td class="num">
                                <div class="d-inline-flex gap-1">
                                    <a class="icon-btn" href="{{ route('products.show', $product) }}" target="_blank" rel="noopener" title="Lihat di toko" aria-label="Lihat {{ $product->name }} di toko"><i class="ph ph-arrow-up-right"></i></a>
                                    <a class="icon-btn" href="{{ route('admin.products.edit', $product) }}" title="Edit" aria-label="Edit {{ $product->name }}"><i class="ph ph-pencil-simple"></i></a>
                                    <form method="POST" action="{{ route('admin.products.destroy', $product) }}" data-confirm="Hapus {{ $product->name }} dari katalog? Riwayat pesanan tetap tersimpan.">
                                        @csrf @method('DELETE')
                                        <button class="icon-btn icon-btn--danger" type="submit" title="Hapus" aria-label="Hapus {{ $product->name }}"><i class="ph ph-trash"></i></button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center py-5">
                                <p class="muted mb-2">Tidak ada produk yang cocok dengan filter ini.</p>
                                <a class="btn-fm btn-fm--ghost btn-fm--sm" href="{{ route('admin.products.index') }}">Reset filter</a>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{ $products->links() }}
@endsection
