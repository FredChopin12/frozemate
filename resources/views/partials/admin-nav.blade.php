@php
    $links = [
        ['Ringkasan', null],
        ['admin.dashboard', 'Dashboard', 'ph-gauge', 'admin.dashboard', null],
        ['admin.reports', 'Laporan penjualan', 'ph-chart-bar', 'admin.reports', null],
        ['Operasional', null],
        ['admin.orders.index', 'Pesanan', 'ph-receipt', 'admin.orders.*', $navPending ?: null],
        ['admin.stock.index', 'Stok', 'ph-stack', 'admin.stock.*', $navLowStock ?: null],
        ['Katalog', null],
        ['admin.products.index', 'Produk', 'ph-package', 'admin.products.*', null],
        ['admin.categories.index', 'Kategori', 'ph-tag', 'admin.categories.*', null],
    ];
@endphp

<ul class="admin-nav">
    @foreach ($links as $link)
        @if ($link[1] === null)
            <li class="admin-nav__label">{{ $link[0] }}</li>
        @else
            <li>
                <a href="{{ route($link[0]) }}" @class(['is-active' => request()->routeIs($link[3])])>
                    <i class="ph {{ $link[2] }}"></i> {{ $link[1] }}
                    @if ($link[4]) <span class="badge-count">{{ $link[4] }}</span> @endif
                </a>
            </li>
        @endif
    @endforeach
</ul>

<div class="admin-side__foot">
    <div class="admin-user">
        <span class="avatar">{{ mb_strtoupper(mb_substr(auth()->user()->name, 0, 1)) }}</span>
        <span>{{ auth()->user()->name }}<small>Admin toko</small></span>
    </div>
    <ul class="admin-nav">
        <li><a href="{{ route('home') }}"><i class="ph ph-storefront"></i> Lihat toko</a></li>
        <li>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <a href="#" onclick="event.preventDefault(); this.closest('form').submit();"><i class="ph ph-sign-out"></i> Keluar</a>
            </form>
        </li>
    </ul>
</div>
