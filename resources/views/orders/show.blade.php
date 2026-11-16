@extends('layouts.app')

@section('title', 'Pesanan #'.$order->code)

@use('App\Enums\OrderStatus')

@php
    $store = config('frozemate.store');
    $cancelled = $order->status === OrderStatus::Cancelled;
@endphp

@section('content')
    <div class="container">
        <div class="page-head">
            <nav class="crumbs" aria-label="Breadcrumb">
                <a href="{{ route('home') }}">Beranda</a> / <a href="{{ route('orders.index') }}">Pesanan saya</a> / <span>#{{ $order->code }}</span>
            </nav>

            @if ($placed)
                <span class="sticker mt-3">Pesanan diterima</span>
                <h1>Terima kasih, {{ strtok($order->customer_name, ' ') }}!</h1>
                <p>Pesanan <strong>#{{ $order->code }}</strong> sudah masuk ke toko. Kami kabari lewat WhatsApp saat mulai diproses.</p>
            @else
                <div class="d-flex align-items-center gap-3 flex-wrap mt-2">
                    <h1 class="m-0">Pesanan #{{ $order->code }}</h1>
                    <x-status :status="$order->status" />
                </div>
                <p>Dibuat {{ $order->created_at->translatedFormat('l, d F Y · H.i') }}</p>
            @endif
        </div>

        <div class="row g-4">
            <div class="col-lg-7">
                {{-- Status --}}
                <div class="card-fm mb-3">
                    <div class="card-fm__body">
                        @if ($cancelled)
                            <div class="notice notice--danger">
                                <i class="ph-fill ph-x-circle"></i>
                                <div><strong>Pesanan dibatalkan.</strong> Hubungi toko di WhatsApp {{ $store['whatsapp'] }} kalau ini tidak seharusnya terjadi.</div>
                            </div>
                        @else
                            <div class="tracker">
                                @foreach (OrderStatus::flow() as $index => $step)
                                    <div @class(['tracker__step', 'is-done' => $index < $order->status->step() || $order->status === OrderStatus::Completed, 'is-current' => $index === $order->status->step() && $order->status !== OrderStatus::Completed])>
                                        <span class="node"><i class="ph-bold ph-check"></i></span>
                                        <strong>{{ $step->label() }}</strong>
                                    </div>
                                @endforeach
                            </div>
                            <p class="text-center muted mt-3 mb-0">{{ $order->status->description() }}</p>
                        @endif
                    </div>
                </div>

                {{-- Instruksi pembayaran --}}
                @unless ($cancelled || $order->status === OrderStatus::Completed)
                    <div class="notice notice--info mb-3">
                        <i class="ph-fill ph-wallet"></i>
                        <div>
                            <strong>{{ $order->paymentLabel() }}</strong><br>
                            @switch($order->payment_method)
                                @case('transfer')
                                    Transfer {{ rupiah($order->total) }} ke <strong>{{ $store['bank'] }}</strong>, lalu kirim bukti ke WhatsApp {{ $store['whatsapp'] }}.
                                    @break
                                @case('qris')
                                    Scan QRIS di kasir saat mengambil pesanan. Siapkan {{ rupiah($order->total) }}.
                                    @break
                                @default
                                    Siapkan uang tunai {{ rupiah($order->total) }} saat pesanan diterima.
                            @endswitch
                        </div>
                    </div>
                @endunless

                {{-- Isi pesanan --}}
                @foreach ($groups as $bundleName => $items)
                    <section class="group-card">
                        <header class="group-card__head">
                            <h4>
                                @if ($bundleName !== '')
                                    <span class="sticker sticker--flat">Bundel</span> {{ $bundleName }}
                                @else
                                    Produk satuan
                                @endif
                            </h4>
                            <span class="mono muted" style="font-size:.76rem">{{ rupiah($items->sum('subtotal')) }}</span>
                        </header>
                        <div class="group-card__body">
                            @foreach ($items as $item)
                                <div class="line-item">
                                    <x-pack :product="$item->product" size="thumb" />
                                    <div class="line-item__info">
                                        <span class="line-item__name">{{ $item->product_name }}</span>
                                        <div class="line-item__meta">{{ $item->quantity }} × {{ rupiah($item->price) }}</div>
                                    </div>
                                    <div class="line-item__price">{{ rupiah($item->subtotal) }}</div>
                                </div>
                            @endforeach
                        </div>
                    </section>
                @endforeach
            </div>

            <div class="col-lg-5">
                <div class="sticky-side">
                    <div class="receipt-wrap">
                        <div class="receipt">
                            <div class="receipt__head">
                                <div class="title">#{{ $order->code }}</div>
                                <div class="sub">FrozeMate · {{ $order->created_at->translatedFormat('d M Y H.i') }}</div>
                            </div>
                            @foreach ($order->items as $item)
                                <div class="receipt__row">
                                    <span class="q">{{ $item->quantity }}×</span>
                                    <span class="n">{{ $item->product_name }}</span>
                                    <span class="dots"></span>
                                    <span>{{ rupiah($item->subtotal) }}</span>
                                </div>
                            @endforeach
                            <div class="receipt__rule"></div>
                            <div class="receipt__total">
                                <span class="label">Total</span>
                                <span class="amount">{{ rupiah($order->total) }}</span>
                            </div>
                            <div class="receipt__note">{{ $order->paymentLabel() }}</div>
                            <div class="barcode"></div>
                        </div>
                    </div>

                    <div class="card-fm mt-3">
                        <div class="card-fm__body">
                            <dl class="dl-fm">
                                <dt>Penerima</dt><dd>{{ $order->customer_name }}</dd>
                                <dt>Telepon</dt><dd>{{ $order->phone }}</dd>
                                <dt>Alamat</dt><dd>{{ $order->address }}</dd>
                                @if ($order->notes)
                                    <dt>Catatan</dt><dd>{{ $order->notes }}</dd>
                                @endif
                            </dl>
                        </div>
                    </div>

                    <div class="d-flex gap-2 mt-3 flex-wrap">
                        <a class="btn-fm btn-fm--ghost" href="{{ route('orders.index') }}">Semua pesanan</a>
                        <a class="btn-fm" href="{{ route('products.index') }}">Belanja lagi</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
