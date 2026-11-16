@extends('layouts.app')

@section('title', 'Racik bundel')
@section('body-class', 'has-mobile-bar')

@section('content')
    <div class="container" id="bundle-builder"
         data-url="{{ route('bundle.update') }}"
         data-summary="{{ json_encode($summary) }}">

        <div class="page-head">
            <nav class="crumbs" aria-label="Breadcrumb">
                <a href="{{ route('home') }}">Beranda</a> / <a href="{{ route('bundle.index') }}">Smart Bundle</a> / <span>Racik</span>
            </nav>
            <h1>{{ $summary['name'] }}</h1>
            <p>
                @if ($summary['need'])
                    Untuk {{ $summary['need'] }} · {{ $summary['people'] }} orang · {{ $summary['meals'] }}× makan.
                @endif
                Ganti, tambah, atau hapus produk — total dihitung ulang otomatis.
            </p>
        </div>

        <div class="row g-4">
            <div class="col-lg-7">
                <div class="card-fm">
                    <div class="card-fm__head">
                        <h3>Isi bundel <span class="mono muted fw-normal" style="font-size:.8rem">· <span data-count>{{ $summary['count'] }}</span> kemasan</span></h3>
                        <button type="button" class="btn-fm btn-fm--sm" data-open-picker><i class="ph-bold ph-plus"></i> Tambah produk</button>
                    </div>
                    <div class="card-fm__body py-2">
                        <div data-lines></div>

                        <div class="text-center py-5" data-empty hidden>
                            <div class="empty__icon"><i class="ph ph-package"></i></div>
                            <h4>Bundel masih kosong</h4>
                            <p class="muted mb-3">Tambahkan produk pertama untuk mulai meracik.</p>
                            <button type="button" class="btn-fm btn-fm--primary" data-open-picker><i class="ph-bold ph-plus"></i> Pilih produk</button>
                        </div>
                    </div>
                </div>

                @unless ($fromRecommendation)
                    <div class="notice notice--info mt-3">
                        <i class="ph-fill ph-info"></i>
                        <div>Mau dipilihkan dulu? <a href="{{ route('bundle.index') }}">Isi Smart Bundle</a> dan kami siapkan isinya sesuai budget.</div>
                    </div>
                @endunless
            </div>

            <div class="col-lg-5">
                <div class="sticky-side">
                    <div class="receipt-wrap">
                        <div class="receipt">
                            <div class="receipt__head">
                                <div class="title">{{ $summary['name'] }}</div>
                                <div class="sub">Racikan · {{ now()->translatedFormat('d M Y') }}</div>
                            </div>
                            <div data-receipt-rows></div>
                            <div class="receipt__rule"></div>
                            <div class="receipt__total">
                                <span class="label">Total</span>
                                <span class="amount" data-total>{{ $summary['total_label'] }}</span>
                            </div>
                            <div class="receipt__note" data-saving hidden>menghitung ulang…</div>
                        </div>
                    </div>

                    <div class="card-fm mt-3">
                        <div class="card-fm__body">
                            @if ($summary['budget'])
                                <div class="meter-row">
                                    <span>Budget</span>
                                    <strong>{{ $summary['budget']['label'] }}</strong>
                                </div>
                                <div class="meter" data-budget-meter><span style="width:{{ $summary['budget']['percent'] }}%"></span></div>
                                <div class="meter-note mb-3" data-budget-note>{{ $summary['budget']['message'] }}</div>
                            @else
                                <div class="meter-note mb-3" data-budget-note></div>
                            @endif

                            @if ($summary['target'])
                                <div class="meter-row">
                                    <span>Porsi</span>
                                    <strong data-meals-value>±{{ $summary['meals_covered'] }}× makan</strong>
                                </div>
                                <div class="meter" data-meals-meter><span style="width:{{ $summary['coverage'] }}%"></span></div>
                                <div class="meter-note mb-3" data-meals-note></div>
                            @endif

                            <form method="POST" action="{{ route('bundle.customize.add') }}" data-bundle-submit>
                                @csrf
                                <button class="btn-fm btn-fm--primary btn-fm--lg btn-fm--block" type="submit" data-submit-bundle>
                                    <i class="ph-bold ph-basket"></i> Masukkan ke keranjang
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Bar bawah di layar kecil --}}
    <div class="mobile-bar">
        <div>
            <small><span data-count>{{ $summary['count'] }}</span> kemasan</small>
            <span class="amount" data-total>{{ $summary['total_label'] }}</span>
        </div>
        <form method="POST" action="{{ route('bundle.customize.add') }}" data-bundle-submit>
            @csrf
            <button class="btn-fm btn-fm--primary" type="submit" data-submit-bundle><i class="ph-bold ph-basket"></i> Ke keranjang</button>
        </form>
    </div>

    {{-- Pemilih produk --}}
    <div class="offcanvas offcanvas-end" tabindex="-1" id="picker" aria-labelledby="pickerTitle" style="--bs-offcanvas-width:min(440px,100vw)">
        <div class="offcanvas-header">
            <h3 class="mb-0" id="pickerTitle" data-picker-title>Tambah produk</h3>
            <button type="button" class="icon-btn" data-bs-dismiss="offcanvas" aria-label="Tutup"><i class="ph-bold ph-x"></i></button>
        </div>
        <div class="px-3 pb-2">
            <div class="input-affix input-affix--icon mb-2">
                <i class="affix ph ph-magnifying-glass"></i>
                <input type="search" class="form-control" placeholder="Cari produk…" data-picker-search aria-label="Cari produk">
            </div>
            <div class="chip-scroll">
                <button type="button" class="chip is-active" data-picker-cat="">Semua</button>
                @foreach ($categories as $category)
                    <button type="button" class="chip" data-picker-cat="{{ $category->name }}">{{ $category->name }}</button>
                @endforeach
            </div>
        </div>
        <div class="offcanvas-body pt-2">
            <div class="picker-list">
                @foreach ($products as $product)
                    <button type="button" class="picker-item"
                            data-id="{{ $product->id }}"
                            data-name="{{ $product->name }}"
                            data-price="{{ $product->price }}"
                            data-stock="{{ $product->stock }}"
                            data-weight="{{ $product->weightLabel() }}"
                            data-category="{{ $product->category->name }}">
                        <x-pack :product="$product" size="mini" />
                        <span>
                            <span class="line-item__name" style="font-size:.95rem">{{ $product->name }}</span>
                            <span class="line-item__meta d-block">{{ rupiah($product->price) }} · {{ $product->servings }} porsi</span>
                            <span class="delta d-block"></span>
                        </span>
                        <span class="plus"><i class="ph-bold ph-plus"></i></span>
                    </button>
                @endforeach
            </div>
            <p class="muted text-center py-4 mb-0" data-picker-empty hidden>Tidak ada produk yang cocok.</p>
        </div>
    </div>
@endsection
