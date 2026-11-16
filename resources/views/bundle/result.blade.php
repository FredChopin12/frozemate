@extends('layouts.app')

@section('title', 'Rekomendasi bundel')

@php
    $main = $bundles->first();
    $alternatives = $bundles->skip(1);
    $peopleLabel = config("frozemate.people.{$params['people']}");
@endphp

@section('content')
    <div class="container">
        <div class="page-head">
            <nav class="crumbs" aria-label="Breadcrumb">
                <a href="{{ route('home') }}">Beranda</a> / <a href="{{ route('bundle.index') }}">Smart Bundle</a> / <span>Rekomendasi</span>
            </nav>

            <div class="d-flex flex-wrap align-items-center gap-2 mt-3">
                <span class="tag"><i class="ph {{ $need['icon'] }}"></i> {{ $need['label'] }}</span>
                <span class="tag">{{ $peopleLabel }} orang</span>
                <span class="tag">{{ $params['meals'] }}× makan</span>
                <span class="tag">{{ $budget['long'] }}</span>
                <a class="link-arrow ms-1" style="font-size:.88rem" href="{{ route('bundle.index', $params) }}">Ubah <i class="ph-bold ph-sliders-horizontal"></i></a>
            </div>
        </div>

        @if (! $main)
            <div class="empty">
                <div class="empty__icon"><i class="ph ph-snowflake"></i></div>
                <h3>Belum bisa meracik bundel</h3>
                <p>Stok produk sedang kosong atau budget terlalu kecil untuk produk apa pun. Coba naikkan budget.</p>
                <a class="btn-fm btn-fm--primary" href="{{ route('bundle.index', $params) }}">Ubah pilihan</a>
            </div>
        @else
            @if ($main['coverage'] < 100)
                <div class="notice mb-4">
                    <i class="ph-fill ph-warning"></i>
                    <div>
                        <strong>Budget ini belum cukup untuk semua porsi.</strong>
                        Bundel terbaik menutup sekitar {{ $main['meals_covered'] }} dari {{ $params['meals'] }} kali makan.
                        <a href="{{ route('bundle.index', $params) }}">Naikkan budget</a> atau kurangi jumlah makan supaya pas.
                    </div>
                </div>
            @endif

            {{-- Rekomendasi utama --}}
            <article class="reco">
                <header class="reco__head">
                    <div>
                        <span class="sticker sticker--flat mb-2">Rekomendasi utama</span>
                        <h2>{{ $main['name'] }}</h2>
                        <p>{{ $main['tagline'] }}</p>
                    </div>
                    <div class="text-end">
                        <div class="mono" style="font-size:.7rem;letter-spacing:.08em;color:#8fb1bd">TOTAL</div>
                        <div class="price" style="font-size:2.1rem;color:#fff;line-height:1.1">{{ rupiah($main['total']) }}</div>
                    </div>
                </header>

                <div class="reco__body">
                    <div class="row g-4">
                        <div class="col-lg-7">
                            <div class="stagger">
                                @foreach ($main['items'] as $item)
                                    <div class="line-item">
                                        <x-pack :product="$item['product']" size="thumb" />
                                        <div class="line-item__info">
                                            <a class="line-item__name" href="{{ route('products.show', $item['product']) }}">{{ $item['product']->name }}</a>
                                            <div class="line-item__meta">
                                                {{ $item['qty'] }} × {{ rupiah($item['product']->price) }} · {{ $item['product']->weightLabel() }} · {{ $item['product']->servings * $item['qty'] }} porsi
                                            </div>
                                        </div>
                                        <div class="line-item__price">{{ rupiah($item['subtotal']) }}</div>
                                    </div>
                                @endforeach
                            </div>
                        </div>

                        <div class="col-lg-5">
                            <div class="card-fm" style="background:var(--ice);box-shadow:none">
                                <div class="card-fm__body">
                                    <div class="kv mb-3">
                                        <div><strong>±{{ $main['meals_covered'] }}×</strong>makan tercukupi</div>
                                        <div><strong>{{ $main['items']->sum('qty') }}</strong>kemasan</div>
                                        <div><strong>{{ $main['categories'] }}</strong>jenis</div>
                                    </div>

                                    <div class="meter-row">
                                        <span>Pemakaian budget</span>
                                        <strong>{{ rupiah($main['total']) }}@if ($main['budget_max']) <span class="muted fw-normal">/ {{ rupiah($main['budget_max']) }}</span>@endif</strong>
                                    </div>
                                    <div class="meter">
                                        <span style="width:{{ $main['budget_max'] ? min(100, round($main['total'] / $main['budget_max'] * 100)) : 100 }}%"></span>
                                    </div>
                                    <div class="meter-note">
                                        @if ($main['budget_max'])
                                            Sisa {{ rupiah($main['budget_max'] - $main['total']) }} dari batas budget.
                                        @else
                                            Masuk rentang budgetmu ({{ lcfirst($budget['long']) }}).
                                        @endif
                                    </div>

                                    <div class="d-grid gap-2 mt-4">
                                        <form method="POST" action="{{ route('bundle.add') }}">
                                            @csrf
                                            @foreach ($params as $key => $value) <input type="hidden" name="{{ $key }}" value="{{ $value }}"> @endforeach
                                            <input type="hidden" name="variant" value="{{ $main['key'] }}">
                                            <button class="btn-fm btn-fm--primary btn-fm--lg btn-fm--block" type="submit">
                                                <i class="ph-bold ph-basket"></i> Masukkan ke keranjang
                                            </button>
                                        </form>
                                        <form method="POST" action="{{ route('bundle.choose') }}">
                                            @csrf
                                            @foreach ($params as $key => $value) <input type="hidden" name="{{ $key }}" value="{{ $value }}"> @endforeach
                                            <input type="hidden" name="variant" value="{{ $main['key'] }}">
                                            <button class="btn-fm btn-fm--ghost btn-fm--block" type="submit">
                                                <i class="ph-bold ph-swap"></i> Racik dulu: ganti, tambah, hapus
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </article>

            {{-- Pilihan lain --}}
            @if ($alternatives->isNotEmpty())
                <section class="section pb-0">
                    <div class="section-head">
                        <div>
                            <span class="eyebrow">Pilihan lain</span>
                            <h2 class="mt-2 mb-0">Sama kebutuhannya, beda gayanya</h2>
                        </div>
                    </div>

                    <div class="row g-3 g-lg-4">
                        @foreach ($alternatives as $bundle)
                            <div class="col-md-6">
                                <article class="alt-card">
                                    <div class="d-flex justify-content-between align-items-start gap-3">
                                        <div>
                                            <span class="tag">{{ $bundle['key'] === 'hemat' ? 'Paling hemat' : 'Paling beragam' }}</span>
                                            <h3>{{ $bundle['name'] }}</h3>
                                            <p class="muted mb-0" style="font-size:.9rem">{{ $bundle['tagline'] }}</p>
                                        </div>
                                        <div class="text-end">
                                            <div class="price" style="font-size:1.5rem;line-height:1.1">{{ rupiah($bundle['total']) }}</div>
                                            @if ($bundle['total'] < $main['total'])
                                                <div class="mono" style="font-size:.7rem;color:var(--leaf)">hemat {{ rupiah($main['total'] - $bundle['total']) }}</div>
                                            @endif
                                        </div>
                                    </div>

                                    <ul class="alt-card__items">
                                        @foreach ($bundle['items'] as $item)
                                            <li><span>{{ $item['qty'] }}× {{ $item['product']->name }}</span><span>{{ rupiah($item['subtotal']) }}</span></li>
                                        @endforeach
                                    </ul>

                                    <div class="d-flex align-items-center justify-content-between gap-2 flex-wrap mt-auto">
                                        <span class="mono muted" style="font-size:.74rem">±{{ $bundle['meals_covered'] }}× makan · {{ $bundle['categories'] }} jenis</span>
                                        <div class="d-flex gap-2">
                                            <form method="POST" action="{{ route('bundle.choose') }}">
                                                @csrf
                                                @foreach ($params as $key => $value) <input type="hidden" name="{{ $key }}" value="{{ $value }}"> @endforeach
                                                <input type="hidden" name="variant" value="{{ $bundle['key'] }}">
                                                <button class="btn-fm btn-fm--ghost btn-fm--sm" type="submit">Racik</button>
                                            </form>
                                            <form method="POST" action="{{ route('bundle.add') }}">
                                                @csrf
                                                @foreach ($params as $key => $value) <input type="hidden" name="{{ $key }}" value="{{ $value }}"> @endforeach
                                                <input type="hidden" name="variant" value="{{ $bundle['key'] }}">
                                                <button class="btn-fm btn-fm--sm" type="submit"><i class="ph-bold ph-basket"></i> Ambil ini</button>
                                            </form>
                                        </div>
                                    </div>
                                </article>
                            </div>
                        @endforeach
                    </div>
                </section>
            @endif
        @endif
    </div>
@endsection
