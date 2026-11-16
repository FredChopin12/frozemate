@extends('layouts.admin')

@section('title', 'Kategori')
@section('eyebrow', 'Katalog')
@section('heading', 'Kategori')

@section('content')
    <div class="row g-3">
        <div class="col-xl-8">
            <div class="card-fm">
                <div class="card-fm__head">
                    <h3>Daftar kategori</h3>
                    <span class="result-count">{{ $categories->count() }} kategori</span>
                </div>
                <div class="table-wrap">
                    <table class="table-fm">
                        <thead>
                            <tr><th>Kategori</th><th>Peran di bundel</th><th class="num">Produk</th><th class="num">Aksi</th></tr>
                        </thead>
                        <tbody>
                            @forelse ($categories as $item)
                                <tr>
                                    <td>
                                        <div class="cell-product">
                                            <x-pack :tone="$item->tone" :art="$item->art" size="mini" />
                                            <div>
                                                <a class="row-link" href="{{ route('admin.categories.edit', $item) }}">{{ $item->name }}</a>
                                                <small>{{ $item->description ?: '—' }}</small>
                                            </div>
                                        </div>
                                    </td>
                                    <td><span class="tag">{{ $item->is_side ? 'Pendamping' : 'Lauk utama' }}</span></td>
                                    <td class="num">
                                        <a href="{{ route('admin.products.index', ['kategori' => $item->id]) }}">{{ $item->products_count }}</a>
                                    </td>
                                    <td class="num">
                                        <div class="d-inline-flex gap-1">
                                            <a class="icon-btn" href="{{ route('admin.categories.edit', $item) }}" title="Edit" aria-label="Edit {{ $item->name }}"><i class="ph ph-pencil-simple"></i></a>
                                            <form method="POST" action="{{ route('admin.categories.destroy', $item) }}" data-confirm="Hapus kategori {{ $item->name }}?">
                                                @csrf @method('DELETE')
                                                <button class="icon-btn icon-btn--danger" type="submit" title="{{ $item->products_count ? 'Masih berisi produk' : 'Hapus' }}"
                                                        aria-label="Hapus {{ $item->name }}" @disabled($item->products_count > 0)><i class="ph ph-trash"></i></button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="4" class="text-center muted py-5">Belum ada kategori. Buat yang pertama lewat form di samping.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            <p class="form-text mt-2"><i class="ph ph-info"></i> Kategori yang masih berisi produk tidak bisa dihapus.</p>
        </div>

        <div class="col-xl-4">
            <div class="card-fm">
                <div class="card-fm__head"><h3>Kategori baru</h3></div>
                <div class="card-fm__body">
                    <form method="POST" action="{{ route('admin.categories.store') }}" novalidate>
                        @csrf
                        @include('admin.categories._form')
                        <button class="btn-fm btn-fm--primary btn-fm--block" type="submit"><i class="ph-bold ph-plus"></i> Tambah kategori</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
