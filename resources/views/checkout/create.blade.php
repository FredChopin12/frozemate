@extends('layouts.app')

@section('title', 'Checkout')

@php
    $payments = config('frozemate.payments');
    $paymentIcons = ['cod' => 'ph-hand-coins', 'transfer' => 'ph-credit-card', 'qris' => 'ph-qr-code'];
@endphp

@section('content')
    <div class="container">
        <div class="page-head">
            <nav class="crumbs" aria-label="Breadcrumb">
                <a href="{{ route('home') }}">Beranda</a> / <a href="{{ route('cart.index') }}">Keranjang</a> / <span>Checkout</span>
            </nav>
            <h1>Checkout</h1>
        </div>

        <form method="POST" action="{{ route('checkout.store') }}" novalidate>
            @csrf

            <div class="row g-4">
                <div class="col-lg-7">
                    <div class="card-fm mb-3">
                        <div class="card-fm__head"><h3>Penerima</h3><span class="tag">01</span></div>
                        <div class="card-fm__body">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label" for="customer_name">Nama</label>
                                    <input type="text" id="customer_name" name="customer_name" autocomplete="name"
                                           class="form-control @error('customer_name') is-invalid @enderror"
                                           value="{{ old('customer_name', $user->name) }}" required>
                                    @error('customer_name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label" for="phone">Nomor telepon / WhatsApp</label>
                                    <input type="tel" id="phone" name="phone" autocomplete="tel" inputmode="tel" placeholder="0812-3456-7890"
                                           class="form-control @error('phone') is-invalid @enderror"
                                           value="{{ old('phone', $user->phone) }}" required>
                                    @error('phone') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                                <div class="col-12">
                                    <label class="form-label" for="address">Alamat lengkap</label>
                                    <textarea id="address" name="address" rows="3" autocomplete="street-address"
                                              placeholder="Jalan, nomor rumah, RT/RW, kecamatan, dan patokan"
                                              class="form-control @error('address') is-invalid @enderror" required>{{ old('address', $user->address) }}</textarea>
                                    @error('address') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                                <div class="col-12">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="save_profile" value="1" id="save_profile" @checked(old('save_profile', true))>
                                        <label class="form-check-label" for="save_profile">Simpan telepon &amp; alamat untuk pesanan berikutnya</label>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card-fm mb-3">
                        <div class="card-fm__head"><h3>Metode pembayaran</h3><span class="tag">02</span></div>
                        <div class="card-fm__body">
                            <div class="d-grid gap-2">
                                @foreach ($payments as $key => $payment)
                                    <label class="choice">
                                        <input type="radio" name="payment_method" value="{{ $key }}" @checked(old('payment_method', 'cod') === $key) required>
                                        <span class="choice__box">
                                            <i class="ico ph {{ $paymentIcons[$key] ?? 'ph-wallet' }}"></i>
                                            <span>
                                                <strong>{{ $payment['label'] }}</strong>
                                                <small>{{ $payment['hint'] }}</small>
                                            </span>
                                            <span class="radio"></span>
                                        </span>
                                    </label>
                                @endforeach
                            </div>
                            @error('payment_method') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                        </div>
                    </div>

                    <div class="card-fm">
                        <div class="card-fm__head"><h3>Catatan <span class="muted fw-normal" style="font-size:.85rem">(opsional)</span></h3><span class="tag">03</span></div>
                        <div class="card-fm__body">
                            <label class="visually-hidden" for="notes">Catatan untuk toko</label>
                            <input type="text" id="notes" name="notes" maxlength="255" class="form-control @error('notes') is-invalid @enderror"
                                   placeholder="Contoh: antar setelah jam 5 sore, titip di satpam" value="{{ old('notes') }}">
                            @error('notes') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                    </div>
                </div>

                <div class="col-lg-5">
                    <div class="sticky-side">
                        <div class="receipt-wrap">
                            <div class="receipt">
                                <div class="receipt__head">
                                    <div class="title">Pesananmu</div>
                                    <div class="sub">{{ $count }} kemasan · {{ now()->translatedFormat('d M Y') }}</div>
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
                                <div class="barcode"></div>
                            </div>
                        </div>

                        <button class="btn-fm btn-fm--primary btn-fm--lg btn-fm--block mt-3" type="submit">
                            <i class="ph-bold ph-seal-check"></i> Buat pesanan
                        </button>
                        <a class="link-arrow d-table mx-auto mt-3" style="font-size:.9rem" href="{{ route('cart.index') }}"><i class="ph-bold ph-arrow-left"></i> Kembali ke keranjang</a>
                    </div>
                </div>
            </div>
        </form>
    </div>
@endsection
