@extends('layouts.app')

@section('content')
    {{-- Hero: alatnya langsung di depan, bukan sekadar gambar --}}
    <section class="hero">
        <div class="container">
            <div class="row g-5 align-items-center">
                <div class="col-lg-6">
                    <span class="eyebrow">Smart Frozen Food Bundle</span>
                    <h1>Bilang butuhnya apa. Kami <em>racik</em> bundelnya.</h1>
                    <p class="hero__lead">
                        Tidak perlu pilih produk satu-satu sambil buka kalkulator. Sebut untuk siapa, berapa orang,
                        dan berapa budgetnya — FrozeMate menyusun kombinasi frozen food yang pas.
                    </p>
                    <a class="link-arrow mt-2" href="{{ route('products.index') }}">
                        Atau lihat semua produk <i class="ph-bold ph-arrow-right"></i>
                    </a>

                    <div class="hero__facts">
                        <div><strong>{{ $productCount }}</strong>produk beku</div>
                        <div><strong>{{ $categories->count() }}</strong>kategori</div>
                        <div><strong>{{ rupiah($lowestPrice) }}</strong>harga mulai</div>
                    </div>
                </div>

                <div class="col-lg-6">
                    <x-bundle-form id="hero" sticker="Tanpa hitung manual" />
                </div>
            </div>
        </div>
    </section>

    {{-- Cara kerja --}}
    <section class="section--tight">
        <div class="container">
            <div class="steps">
                <div>
                    <span class="no">01 — ISI</span>
                    <h3>Empat pilihan, selesai</h3>
                    <p>Untuk siapa, berapa orang, berapa kali makan, dan rentang budget. Tidak ada formulir panjang.</p>
                </div>
                <div>
                    <span class="no">02 — RACIK</span>
                    <h3>Sistem menyusun bundel</h3>
                    <p>Produk dipilih dari stok yang ada, dihitung porsinya, dan dijaga supaya tidak lewat budget.</p>
                </div>
                <div>
                    <span class="no">03 — UBAH</span>
                    <h3>Ganti sesukamu</h3>
                    <p>Tukar kentang dengan fish ball, tambah satu pack, hapus yang tidak suka. Total ikut berubah.</p>
                </div>
            </div>
        </div>
    </section>

    {{-- Produk --}}
    <section class="section">
        <div class="container">
            <div class="section-head">
                <div>
                    <span class="eyebrow">Dari freezer kami</span>
                    <h2 class="mt-2 mb-0">Yang paling sering dibawa pulang</h2>
                </div>
                <a class="link-arrow" href="{{ route('products.index') }}">Semua produk <i class="ph-bold ph-arrow-right"></i></a>
            </div>

            <div class="chip-scroll mb-4">
                @foreach ($categories as $category)
                    <a class="chip" href="{{ route('products.index', ['kategori' => $category->slug]) }}">
                        {{ $category->name }} <span class="count">{{ $category->products_count }}</span>
                    </a>
                @endforeach
            </div>

            <div class="row g-3 g-lg-4">
                @foreach ($featured as $product)
                    <div class="col-6 col-md-4 col-lg-3">
                        <x-product-card :product="$product" />
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    {{-- Contoh hasil: dihitung sungguhan dari katalog saat ini --}}
    @if ($example)
        <section class="section--tight">
            <div class="container">
                <div class="showcase">
                    <div class="row g-5 align-items-center position-relative">
                        <div class="col-lg-7">
                            <span class="eyebrow">Bukan tebak-tebakan</span>
                            <h2 class="mt-2">Begini bundel dihitung</h2>

                            <div class="showcase__input">
                                <span class="tag">{{ config("frozemate.needs.{$exampleParams['need']}.label") }}</span>
                                <span class="tag">{{ $exampleParams['people'] }} orang</span>
                                <span class="tag">{{ $exampleParams['meals'] }}× makan</span>
                                <span class="tag">{{ config("frozemate.budgets.{$exampleParams['budget']}.long") }}</span>
                                <span class="tag" style="background:var(--butter);color:var(--ink)">→ {{ $example['name'] }}</span>
                            </div>

                            <ul class="rule-list">
                                <li><i class="ph-fill ph-check-circle"></i><span>Target porsi = jumlah orang × jumlah kali makan.</span></li>
                                <li><i class="ph-fill ph-check-circle"></i><span>Hanya produk yang stoknya tersedia, diutamakan yang cocok dengan kebutuhanmu.</span></li>
                                <li><i class="ph-fill ph-check-circle"></i><span>Total tidak pernah melewati batas atas budget.</span></li>
                                <li><i class="ph-fill ph-check-circle"></i><span>Camilan dihitung setengah porsi, supaya bundel tetap berisi lauk.</span></li>
                            </ul>

                            <a class="btn-fm btn-fm--butter mt-4" href="{{ route('bundle.result', $exampleParams) }}">
                                Buka contoh ini <i class="ph-bold ph-arrow-right"></i>
                            </a>
                        </div>

                        <div class="col-lg-5">
                            <div class="receipt-wrap" style="transform:rotate(1.5deg);max-width:360px;margin-inline:auto">
                                <div class="receipt">
                                    <div class="receipt__head">
                                        <div class="title">{{ $example['name'] }}</div>
                                        <div class="sub">FrozeMate · contoh rekomendasi</div>
                                    </div>
                                    @foreach ($example['items'] as $item)
                                        <div class="receipt__row">
                                            <span class="q">{{ $item['qty'] }}×</span>
                                            <span class="n">{{ $item['product']->name }}</span>
                                            <span class="dots"></span>
                                            <span>{{ rupiah($item['subtotal']) }}</span>
                                        </div>
                                    @endforeach
                                    <div class="receipt__rule"></div>
                                    <div class="receipt__total">
                                        <span class="label">Total</span>
                                        <span class="amount">{{ rupiah($example['total']) }}</span>
                                    </div>
                                    <div class="receipt__note">cukup ±{{ $example['meals_covered'] }}× makan · target {{ $exampleParams['meals'] }}×</div>
                                    <div class="barcode"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    @endif

    {{-- Belanja menurut kebutuhan --}}
    <section class="section">
        <div class="container">
            <div class="section-head">
                <div>
                    <span class="eyebrow">Jelajahi sendiri</span>
                    <h2 class="mt-2 mb-0">Belanja menurut kebutuhan</h2>
                </div>
            </div>

            <div class="row g-3">
                @foreach (config('frozemate.needs') as $key => $need)
                    <div class="col-6 col-md-4 col-lg">
                        <a class="need-tile" href="{{ route('products.index', ['kebutuhan' => $key]) }}">
                            <i class="lead ph {{ $need['icon'] }}"></i>
                            <i class="go ph-bold ph-arrow-up-right"></i>
                            <strong>{{ $need['label'] }}</strong>
                            <span>{{ $need['hint'] }}</span>
                        </a>
                    </div>
                @endforeach
            </div>
        </div>
    </section>
@endsection
