@extends('layouts.app')

@section('title', $product->name)

@php $needs = config('frozemate.needs'); @endphp

@section('content')
    <div class="container">
        <div class="page-head pb-3">
            <nav class="crumbs" aria-label="Breadcrumb">
                <a href="{{ route('home') }}">Beranda</a> /
                <a href="{{ route('products.index') }}">Produk</a> /
                <a href="{{ route('products.index', ['kategori' => $product->category->slug]) }}">{{ $product->category->name }}</a>
            </nav>
        </div>

        <div class="row g-4 g-lg-5">
            <div class="col-md-5">
                <x-pack :product="$product" size="hero" />
            </div>

            <div class="col-md-7 col-lg-6">
                <span class="eyebrow">{{ $product->category->name }}</span>
                <h1 class="mt-2 mb-3">{{ $product->name }}</h1>

                <div class="d-flex align-items-end gap-3 flex-wrap mb-3">
                    <span class="price" style="font-size:2.2rem;line-height:1">{{ rupiah($product->price) }}</span>
                    <span class="mono muted" style="font-size:.8rem">≈ {{ rupiah($product->pricePerServing()) }} per porsi</span>
                </div>

                <p style="font-size:1.03rem;color:var(--ink-2);max-width:54ch">{{ $product->description }}</p>

                {{-- Label keterangan, seperti di balik kemasan --}}
                <div class="card-fm my-4">
                    <div class="card-fm__body">
                        <dl class="dl-fm">
                            <dt>Berat bersih</dt>
                            <dd>{{ $product->weightLabel() }}</dd>
                            <dt>Isi</dt>
                            <dd>± {{ $product->servings }} porsi</dd>
                            @if ($product->cook_method)
                                <dt>Cara masak</dt>
                                <dd>{{ $product->cook_method }}</dd>
                            @endif
                            <dt>Simpan</dt>
                            <dd>Freezer −18°C, tahan hingga 3 bulan</dd>
                            @if ($product->tags)
                                <dt>Cocok untuk</dt>
                                <dd class="d-flex flex-wrap gap-1">
                                    @foreach ($product->tags as $tag)
                                        @isset($needs[$tag])
                                            <a class="tag text-decoration-none" href="{{ route('products.index', ['kebutuhan' => $tag]) }}">{{ $needs[$tag]['label'] }}</a>
                                        @endisset
                                    @endforeach
                                </dd>
                            @endif
                        </dl>
                    </div>
                </div>

                @if ($product->isSoldOut())
                    <div class="notice"><i class="ph-fill ph-warning"></i><div><strong>Stok sedang habis.</strong> Coba produk sejenis di bawah, atau racik bundel tanpa produk ini.</div></div>
                @else
                    <form method="POST" action="{{ route('cart.store') }}" class="d-flex flex-wrap align-items-center gap-3">
                        @csrf
                        <input type="hidden" name="product_id" value="{{ $product->id }}">
                        <x-qty :max="min($product->stock, 50)" />
                        <button class="btn-fm btn-fm--primary btn-fm--lg" type="submit">
                            <i class="ph-bold ph-basket"></i> Tambah ke keranjang
                        </button>
                    </form>
                    <p class="mono mt-3 mb-0" style="font-size:.76rem;color:{{ $product->isLowStock() ? 'var(--ember-dark)' : 'var(--mute)' }}">
                        @if ($product->isLowStock())
                            Stok menipis — tinggal {{ $product->stock }} kemasan.
                        @else
                            Stok tersedia: {{ $product->stock }} kemasan.
                        @endif
                    </p>
                @endif
            </div>
        </div>

        @if ($related->isNotEmpty())
            <section class="section pb-0">
                <div class="section-head">
                    <h2 class="mb-0">Masih di rak {{ $product->category->name }}</h2>
                    <a class="link-arrow" href="{{ route('products.index', ['kategori' => $product->category->slug]) }}">Lihat semua <i class="ph-bold ph-arrow-right"></i></a>
                </div>
                <div class="row g-3 g-lg-4">
                    @foreach ($related as $item)
                        <div class="col-6 col-md-4 col-lg-3">
                            <x-product-card :product="$item" />
                        </div>
                    @endforeach
                </div>
            </section>
        @endif
    </div>
@endsection
