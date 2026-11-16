@extends('layouts.app')

@section('title', 'Keranjang')

@section('content')
    <div class="container">
        <div class="page-head">
            <nav class="crumbs" aria-label="Breadcrumb"><a href="{{ route('home') }}">Beranda</a> / <span>Keranjang</span></nav>
            <h1>Keranjang</h1>
        </div>

        @if ($groups->isEmpty())
            <div class="empty">
                <div class="empty__icon"><i class="ph ph-basket"></i></div>
                <h3>Keranjangmu masih kosong</h3>
                <p>Mulai dari Smart Bundle supaya tidak perlu pilih satu-satu, atau jelajahi katalognya.</p>
                <div class="d-flex gap-2 justify-content-center flex-wrap">
                    <a class="btn-fm btn-fm--primary" href="{{ route('bundle.index') }}">Racik bundel <i class="ph-bold ph-arrow-right"></i></a>
                    <a class="btn-fm btn-fm--ghost" href="{{ route('products.index') }}">Lihat produk</a>
                </div>
            </div>
        @else
            @if ($issues->isNotEmpty())
                <div class="notice notice--danger mb-3">
                    <i class="ph-fill ph-warning-circle"></i>
                    <div><strong>Stok berubah.</strong> Kurangi jumlah untuk: {{ $issues->implode(', ') }}.</div>
                </div>
            @endif

            <div class="row g-4">
                <div class="col-lg-7">
                    @foreach ($groups as $bundleName => $lines)
                        <section class="group-card">
                            <header class="group-card__head">
                                <h4>
                                    @if ($bundleName !== '')
                                        <span class="sticker sticker--flat">Bundel</span> {{ $bundleName }}
                                    @else
                                        Produk satuan
                                    @endif
                                </h4>
                                <span class="mono muted" style="font-size:.76rem">{{ rupiah($lines->sum('subtotal')) }}</span>
                            </header>
                            <div class="group-card__body">
                                @foreach ($lines as $line)
                                    <div class="line-item">
                                        <x-pack :product="$line->product" size="thumb" />
                                        <div class="line-item__info">
                                            <a class="line-item__name" href="{{ route('products.show', $line->product) }}">{{ $line->product->name }}</a>
                                            <div class="line-item__meta">{{ rupiah($line->product->price) }} · {{ $line->product->weightLabel() }}</div>
                                        </div>
                                        <div class="line-item__controls d-flex align-items-center gap-2">
                                            <form method="POST" action="{{ route('cart.update', $line->key) }}" data-autosubmit>
                                                @csrf @method('PATCH')
                                                <x-qty small :value="$line->qty" :min="1" :max="min($line->product->stock, 50)" />
                                            </form>
                                            <form method="POST" action="{{ route('cart.destroy', $line->key) }}">
                                                @csrf @method('DELETE')
                                                <button class="icon-btn icon-btn--danger" type="submit" aria-label="Hapus {{ $line->product->name }}"><i class="ph ph-trash"></i></button>
                                            </form>
                                        </div>
                                        <div class="line-item__price">{{ rupiah($line->subtotal) }}</div>
                                    </div>
                                @endforeach
                            </div>
                        </section>
                    @endforeach

                    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
                        <a class="link-arrow" href="{{ route('products.index') }}"><i class="ph-bold ph-plus"></i> Tambah produk lain</a>
                        <form method="POST" action="{{ route('cart.clear') }}" data-confirm="Kosongkan semua isi keranjang?">
                            @csrf @method('DELETE')
                            <button class="btn-fm btn-fm--danger btn-fm--sm" type="submit"><i class="ph ph-trash"></i> Kosongkan</button>
                        </form>
                    </div>
                </div>

                <div class="col-lg-5">
                    <div class="sticky-side">
                        <div class="receipt-wrap">
                            <div class="receipt">
                                <div class="receipt__head">
                                    <div class="title">Ringkasan belanja</div>
                                    <div class="sub">{{ $count }} kemasan</div>
                                </div>
                                @foreach ($groups as $bundleName => $lines)
                                    <div class="receipt__group">{{ $bundleName !== '' ? $bundleName : 'Produk satuan' }}</div>
                                    @foreach ($lines as $line)
                                        <div class="receipt__row">
                                            <span class="q">{{ $line->qty }}×</span>
                                            <span class="n">{{ $line->product->name }}</span>
                                            <span class="dots"></span>
                                            <span>{{ rupiah($line->subtotal) }}</span>
                                        </div>
                                    @endforeach
                                @endforeach
                                <div class="receipt__rule"></div>
                                <div class="receipt__total">
                                    <span class="label">Total</span>
                                    <span class="amount">{{ rupiah($total) }}</span>
                                </div>
                                <div class="receipt__note">stok baru dikunci setelah checkout</div>
                            </div>
                        </div>

                        <a @class(['btn-fm btn-fm--primary btn-fm--lg btn-fm--block mt-3', 'is-disabled' => $issues->isNotEmpty()]) href="{{ route('checkout.create') }}">
                            Lanjut ke checkout <i class="ph-bold ph-arrow-right"></i>
                        </a>
                        @guest
                            <p class="mono muted text-center mt-2 mb-0" style="font-size:.74rem">Kamu akan diminta masuk dulu. Isi keranjang tetap aman.</p>
                        @endguest
                    </div>
                </div>
            </div>
        @endif
    </div>
@endsection
