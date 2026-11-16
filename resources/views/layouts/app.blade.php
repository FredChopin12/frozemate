<!DOCTYPE html>
<html lang="id">
<head>
    @include('partials.head')
</head>
<body class="@yield('body-class')">
    @php $store = config('frozemate.store'); @endphp

    <div class="topstrip">
        <div class="container d-flex justify-content-between gap-3">
            <span><span class="temp">−18°C</span> · Dikemas beku, diantar dingin se-{{ $store['city'] }}</span>
            <span class="d-none d-md-inline">{{ $store['hours'] }} · WA {{ $store['whatsapp'] }}</span>
        </div>
    </div>

    <header class="nav-fm">
        <div class="container nav-fm__inner">
            <button class="icon-btn nav-toggle" type="button" data-bs-toggle="offcanvas" data-bs-target="#mobileNav" aria-controls="mobileNav" aria-label="Buka menu">
                <i class="ph-bold ph-list"></i>
            </button>

            <x-brand />

            <ul class="nav-fm__links">
                <li><a href="{{ route('products.index') }}" @class(['is-active' => request()->routeIs('products.*')])>Produk</a></li>
                <li>
                    <a href="{{ route('bundle.index') }}" @class(['is-active' => request()->routeIs('bundle.*')])>
                        Smart Bundle <span class="pill-new">racik</span>
                    </a>
                </li>
                @auth
                    <li><a href="{{ route('orders.index') }}" @class(['is-active' => request()->routeIs('orders.*')])>Pesanan</a></li>
                @endauth
            </ul>

            <form class="nav-search" action="{{ route('products.index') }}" method="GET" role="search">
                <i class="ph ph-magnifying-glass"></i>
                <input type="search" name="q" value="{{ request()->routeIs('products.index') ? request('q') : '' }}" placeholder="Cari nugget, dimsum, sosis…" aria-label="Cari produk">
            </form>

            <a class="cart-btn" href="{{ route('cart.index') }}" aria-label="Keranjang, {{ $cartCount }} barang">
                <i class="ph-bold ph-basket"></i>
                <span class="label">Keranjang</span>
                <span class="cart-count" data-empty="{{ $cartCount ? 0 : 1 }}">{{ $cartCount }}</span>
            </a>

            <div class="account-wrap">
                @auth
                    <div class="dropdown">
                        <button class="account-btn" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <span class="avatar">{{ mb_strtoupper(mb_substr(auth()->user()->name, 0, 1)) }}</span>
                            {{ auth()->user()->firstName() }}
                            <i class="ph-bold ph-caret-down" style="font-size:.75rem"></i>
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end">
                            @if (auth()->user()->isAdmin())
                                <li><a class="dropdown-item" href="{{ route('admin.dashboard') }}"><i class="ph ph-gauge"></i> Dashboard admin</a></li>
                                <li><hr class="dropdown-divider"></li>
                            @endif
                            <li><a class="dropdown-item" href="{{ route('orders.index') }}"><i class="ph ph-receipt"></i> Pesanan saya</a></li>
                            <li>
                                <form method="POST" action="{{ route('logout') }}">
                                    @csrf
                                    <button class="dropdown-item" type="submit"><i class="ph ph-sign-out"></i> Keluar</button>
                                </form>
                            </li>
                        </ul>
                    </div>
                @else
                    <a class="btn-fm btn-fm--ghost btn-fm--sm" href="{{ route('login') }}" style="height:40px">Masuk</a>
                @endauth
            </div>
        </div>
    </header>

    {{-- Menu mobile --}}
    <div class="offcanvas offcanvas-start" tabindex="-1" id="mobileNav" aria-labelledby="mobileNavLabel">
        <div class="offcanvas-header">
            <x-brand id="mobileNavLabel" />
            <button type="button" class="icon-btn" data-bs-dismiss="offcanvas" aria-label="Tutup"><i class="ph-bold ph-x"></i></button>
        </div>
        <div class="offcanvas-body">
            <form class="nav-search d-block mb-3" style="max-width:none" action="{{ route('products.index') }}" method="GET" role="search">
                <i class="ph ph-magnifying-glass"></i>
                <input type="search" name="q" placeholder="Cari produk…" aria-label="Cari produk">
            </form>
            <ul class="mobile-links">
                <li><a href="{{ route('products.index') }}"><i class="ph ph-squares-four"></i> Semua produk</a></li>
                <li><a href="{{ route('bundle.index') }}"><i class="ph ph-magic-wand"></i> Smart Bundle</a></li>
                <li><a href="{{ route('cart.index') }}"><i class="ph ph-basket"></i> Keranjang ({{ $cartCount }})</a></li>
                @auth
                    <li><a href="{{ route('orders.index') }}"><i class="ph ph-receipt"></i> Pesanan saya</a></li>
                    @if (auth()->user()->isAdmin())
                        <li><a href="{{ route('admin.dashboard') }}"><i class="ph ph-gauge"></i> Dashboard admin</a></li>
                    @endif
                    <li>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit"><i class="ph ph-sign-out"></i> Keluar ({{ auth()->user()->firstName() }})</button>
                        </form>
                    </li>
                @else
                    <li><a href="{{ route('login') }}"><i class="ph ph-sign-in"></i> Masuk</a></li>
                    <li><a href="{{ route('register') }}"><i class="ph ph-user"></i> Daftar akun</a></li>
                @endauth
            </ul>
        </div>
    </div>

    <main>
        @yield('content')
    </main>

    <footer class="footer-fm">
        <div class="container">
            <div class="row g-4">
                <div class="col-lg-5">
                    <x-brand light />
                    <p class="mt-3 mb-0" style="max-width:38ch">
                        Frozen food rumahan dari {{ $store['city'] }}. Bilang butuhnya apa, kami bantu racik kombinasinya — pas di porsi, pas di kantong.
                    </p>
                </div>
                <div class="col-6 col-lg-2">
                    <h5>Belanja</h5>
                    <ul>
                        <li><a href="{{ route('products.index') }}">Semua produk</a></li>
                        <li><a href="{{ route('bundle.index') }}">Smart Bundle</a></li>
                        <li><a href="{{ route('cart.index') }}">Keranjang</a></li>
                    </ul>
                </div>
                <div class="col-6 col-lg-2">
                    <h5>Akun</h5>
                    <ul>
                        @auth
                            <li><a href="{{ route('orders.index') }}">Pesanan saya</a></li>
                        @else
                            <li><a href="{{ route('login') }}">Masuk</a></li>
                            <li><a href="{{ route('register') }}">Daftar</a></li>
                        @endauth
                    </ul>
                </div>
                <div class="col-lg-3">
                    <h5>Toko</h5>
                    <ul>
                        <li>{{ $store['hours'] }}</li>
                        <li>WhatsApp {{ $store['whatsapp'] }}</li>
                        <li>Simpan di freezer −18°C</li>
                    </ul>
                </div>
            </div>
            <div class="footer-fm__bottom">
                <span>© {{ date('Y') }} FrozeMate · Proyek Web Programming</span>
                <span>Laravel · Blade · Bootstrap · MySQL</span>
            </div>
        </div>
    </footer>

    @include('partials.flash')
    @stack('scripts')
</body>
</html>
