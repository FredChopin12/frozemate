@extends('layouts.app')

@section('title', $activeCategory?->name ?? 'Semua produk')

@php
    $needs = config('frozemate.needs');
    $hasFilter = collect($filters)->except('urut')->filter()->isNotEmpty();
    // Bangun URL katalog dengan filter saat ini + perubahan tertentu.
    $with = fn (array $change) => route('products.index', array_filter(array_merge($filters, $change), fn ($v) => $v !== null && $v !== ''));
@endphp

@section('content')
    <div class="container">
        <div class="page-head">
            <nav class="crumbs" aria-label="Breadcrumb">
                <a href="{{ route('home') }}">Beranda</a> / <span>Produk</span>
                @if ($activeCategory) / <span>{{ $activeCategory->name }}</span> @endif
            </nav>
            <h1>
                @if (! empty($filters['q']))
                    Hasil untuk “{{ $filters['q'] }}”
                @else
                    {{ $activeCategory?->name ?? 'Semua produk' }}
                @endif
            </h1>
            @if ($activeCategory?->description)
                <p>{{ $activeCategory->description }}</p>
            @endif
        </div>

        {{-- Kategori --}}
        <div class="chip-scroll mb-3">
            <a @class(['chip', 'is-active' => ! $activeCategory]) href="{{ $with(['kategori' => null]) }}">Semua</a>
            @foreach ($categories as $category)
                <a @class(['chip', 'is-active' => $activeCategory?->is($category)]) href="{{ $with(['kategori' => $category->slug]) }}">
                    {{ $category->name }} <span class="count">{{ $category->products_count }}</span>
                </a>
            @endforeach
        </div>

        {{-- Filter & urutan --}}
        <form class="filter-bar" method="GET" action="{{ route('products.index') }}" id="filterForm">
            @foreach (['q', 'kategori'] as $keep)
                @if (! empty($filters[$keep]))
                    <input type="hidden" name="{{ $keep }}" value="{{ $filters[$keep] }}">
                @endif
            @endforeach

            <select class="form-select" name="kebutuhan" aria-label="Filter kebutuhan" onchange="this.form.submit()">
                <option value="">Semua kebutuhan</option>
                @foreach ($needs as $key => $need)
                    <option value="{{ $key }}" @selected(($filters['kebutuhan'] ?? '') === $key)>Cocok untuk {{ $need['label'] }}</option>
                @endforeach
            </select>

            <select class="form-select" name="urut" aria-label="Urutkan" onchange="this.form.submit()">
                @foreach (['terbaru' => 'Urutan: unggulan', 'termurah' => 'Harga terendah', 'termahal' => 'Harga tertinggi', 'hemat' => 'Termurah per porsi'] as $value => $label)
                    <option value="{{ $value }}" @selected(($filters['urut'] ?? 'terbaru') === $value)>{{ $label }}</option>
                @endforeach
            </select>

            <div class="form-check ms-1">
                <input class="form-check-input" type="checkbox" name="tersedia" value="1" id="onlyAvailable" @checked(! empty($filters['tersedia'])) onchange="this.form.submit()">
                <label class="form-check-label fw-semibold" for="onlyAvailable" style="font-size:.9rem">Stok tersedia saja</label>
            </div>

            <span class="result-count ms-auto">{{ $products->total() }} produk</span>

            @if ($hasFilter)
                <a class="btn-fm btn-fm--ghost btn-fm--sm" href="{{ route('products.index') }}"><i class="ph-bold ph-x"></i> Reset</a>
            @endif

            <noscript><button class="btn-fm btn-fm--sm" type="submit">Terapkan</button></noscript>
        </form>

        @if ($products->isEmpty())
            <div class="empty">
                <div class="empty__icon"><i class="ph ph-magnifying-glass"></i></div>
                <h3>Belum ketemu yang cocok</h3>
                <p>Coba kata kunci lain atau longgarkan filternya. Bisa juga biarkan kami yang memilihkan.</p>
                <div class="d-flex gap-2 justify-content-center flex-wrap">
                    <a class="btn-fm btn-fm--ghost" href="{{ route('products.index') }}">Lihat semua produk</a>
                    <a class="btn-fm btn-fm--primary" href="{{ route('bundle.index') }}">Coba Smart Bundle</a>
                </div>
            </div>
        @else
            <div class="row g-3 g-lg-4">
                @foreach ($products as $product)
                    <div class="col-6 col-md-4 col-lg-3">
                        <x-product-card :product="$product" />
                    </div>
                @endforeach
            </div>

            {{ $products->links() }}
        @endif

        {{-- Ajakan ke Smart Bundle --}}
        <div class="card-fm mt-5 p-4 d-flex flex-wrap align-items-center justify-content-between gap-3">
            <div>
                <span class="eyebrow">Bingung pilih yang mana?</span>
                <h3 class="mt-2 mb-1">Biar kami yang meracik</h3>
                <p class="muted mb-0">Empat pilihan singkat, dan bundel sesuai budgetmu langsung jadi.</p>
            </div>
            <a class="btn-fm btn-fm--primary" href="{{ route('bundle.index', array_filter(['need' => $filters['kebutuhan'] ?? null])) }}">
                Buka Smart Bundle <i class="ph-bold ph-arrow-right"></i>
            </a>
        </div>
    </div>
@endsection
