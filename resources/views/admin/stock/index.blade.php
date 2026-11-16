@extends('layouts.admin')

@section('title', 'Stok')
@section('eyebrow', 'Operasional')
@section('heading', 'Stok')

@section('content')
    <div class="row g-3 mb-3">
        <div class="col-6 col-xl-3">
            <div class="stat">
                <div class="stat__label">Total kemasan <i class="ph ph-stack"></i></div>
                <div class="stat__value">{{ number_format($units, 0, ',', '.') }}</div>
                <div class="stat__foot">di freezer saat ini</div>
            </div>
        </div>
        <div class="col-6 col-xl-3">
            <div class="stat">
                <div class="stat__label">Nilai stok <i class="ph ph-coins"></i></div>
                <div class="stat__value">{{ rupiah($value) }}</div>
                <div class="stat__foot">berdasarkan harga jual</div>
            </div>
        </div>
        <div class="col-6 col-xl-3">
            <div @class(['stat', 'stat--alert' => $counts['menipis'] > 0])>
                <div class="stat__label">Menipis <i class="ph ph-warning"></i></div>
                <div class="stat__value">{{ $counts['menipis'] }}</div>
                <div class="stat__foot">stok ≤ {{ config('frozemate.low_stock') }} kemasan</div>
            </div>
        </div>
        <div class="col-6 col-xl-3">
            <div @class(['stat', 'stat--alert' => $counts['habis'] > 0])>
                <div class="stat__label">Habis <i class="ph ph-prohibit"></i></div>
                <div class="stat__value">{{ $counts['habis'] }}</div>
                <div class="stat__foot">tidak bisa dibeli</div>
            </div>
        </div>
    </div>

    <div class="card-fm">
        <div class="card-fm__head flex-wrap">
            <div class="chip-row">
                @foreach (['semua' => 'Semua', 'menipis' => 'Menipis', 'habis' => 'Habis'] as $key => $label)
                    <a @class(['chip', 'is-active' => $filter === $key]) href="{{ route('admin.stock.index', array_filter(['tampil' => $key === 'semua' ? null : $key, 'q' => request('q')])) }}">
                        {{ $label }} <span class="count">{{ $counts[$key] }}</span>
                    </a>
                @endforeach
            </div>
            <form method="GET" class="input-affix input-affix--icon" style="min-width:220px">
                @if ($filter !== 'semua') <input type="hidden" name="tampil" value="{{ $filter }}"> @endif
                <i class="affix ph ph-magnifying-glass"></i>
                <input type="search" class="form-control" style="height:42px;padding-top:0;padding-bottom:0" name="q" value="{{ request('q') }}" placeholder="Cari produk…" aria-label="Cari produk">
            </form>
        </div>

        <div class="table-wrap">
            <table class="table-fm">
                <thead>
                    <tr><th>Produk</th><th class="num">Stok</th><th>Tambah cepat</th><th>Atur jumlah pasti</th></tr>
                </thead>
                <tbody>
                    @forelse ($products as $product)
                        <tr>
                            <td>
                                <div class="cell-product">
                                    <x-pack :product="$product" size="mini" />
                                    <div>
                                        <a class="row-link" href="{{ route('admin.products.edit', $product) }}">{{ $product->name }}</a>
                                        <small>{{ $product->category->name }} · {{ rupiah($product->price) }}</small>
                                    </div>
                                </div>
                            </td>
                            <td class="num">
                                <span @class(['stock-pill', 'stock-pill--out' => $product->isSoldOut(), 'stock-pill--low' => $product->isLowStock()])>{{ $product->stock }}</span>
                            </td>
                            <td>
                                <form method="POST" action="{{ route('admin.stock.update', $product) }}" class="d-flex gap-1">
                                    @csrf @method('PATCH')
                                    @foreach ([5, 10, 24] as $amount)
                                        <button class="btn-fm btn-fm--ghost btn-fm--sm mono" type="submit" name="adjust" value="{{ $amount }}">+{{ $amount }}</button>
                                    @endforeach
                                </form>
                            </td>
                            <td>
                                <form method="POST" action="{{ route('admin.stock.update', $product) }}" class="d-flex gap-2 align-items-center">
                                    @csrf @method('PATCH')
                                    <x-qty small name="stock" :value="$product->stock" :min="0" :max="100000" />
                                    <button class="btn-fm btn-fm--sm" type="submit">Simpan</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="4" class="text-center muted py-5">Tidak ada produk di tampilan ini.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection
