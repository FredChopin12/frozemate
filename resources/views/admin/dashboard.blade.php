@extends('layouts.admin')

@section('title', 'Dashboard')
@section('eyebrow', now()->translatedFormat('l, d F Y'))
@section('heading', 'Halo, '.auth()->user()->firstName())

@section('actions')
    <a class="btn-fm btn-fm--ghost" href="{{ route('admin.orders.index', ['status' => 'pending']) }}">
        <i class="ph ph-receipt"></i> {{ $waiting }} pesanan menunggu
    </a>
    <a class="btn-fm btn-fm--primary" href="{{ route('admin.products.create') }}"><i class="ph-bold ph-plus"></i> Produk baru</a>
@endsection

@php
    $change = $yesterdaySales > 0 ? (int) round(($todaySales - $yesterdaySales) / $yesterdaySales * 100) : null;
    $topMax = max(1, $topProducts->max('qty'));
@endphp

@section('content')
    {{-- Angka utama --}}
    <div class="row g-3 mb-3">
        <div class="col-sm-6 col-xl-3">
            <div class="stat stat--hero">
                <div class="stat__label">Penjualan hari ini <i class="ph ph-coins"></i></div>
                <div class="stat__value">{{ rupiah($todaySales) }}</div>
                <div class="stat__foot">
                    {{ $todayOrders }} pesanan
                    @if ($change !== null)
                        · <span @class(['delta', 'delta--up' => $change >= 0, 'delta--down' => $change < 0])>
                            <i class="ph-bold {{ $change >= 0 ? 'ph-trend-up' : 'ph-trend-down' }}"></i>{{ abs($change) }}% vs kemarin
                        </span>
                    @endif
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="stat">
                <div class="stat__label">Total pesanan <i class="ph ph-receipt"></i></div>
                <div class="stat__value">{{ number_format($totalOrders, 0, ',', '.') }}</div>
                <div class="stat__foot">{{ $weekOrders }} dalam 7 hari terakhir</div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="stat">
                <div class="stat__label">Total produk <i class="ph ph-package"></i></div>
                <div class="stat__value">{{ $totalProducts }}</div>
                <div class="stat__foot"><a href="{{ route('admin.products.index') }}">Kelola katalog</a></div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div @class(['stat', 'stat--alert' => $lowStockCount > 0])>
                <div class="stat__label">Stok menipis <i class="ph ph-warning"></i></div>
                <div class="stat__value">{{ $lowStockCount }}</div>
                <div class="stat__foot">
                    @if ($lowStockCount > 0)
                        <a href="{{ route('admin.stock.index', ['tampil' => 'menipis']) }}">Perlu diisi ulang</a>
                    @else
                        Semua stok aman
                    @endif
                </div>
            </div>
        </div>
    </div>

    <div class="row g-3 mb-3">
        {{-- Ringkasan penjualan --}}
        <div class="col-xl-8">
            <div class="card-fm h-100">
                <div class="card-fm__head">
                    <div>
                        <h3>Omzet 7 hari terakhir</h3>
                        <div class="mono muted mt-1" style="font-size:.74rem">{{ rupiah($weekRevenue) }} · {{ $weekOrders }} pesanan · tidak termasuk yang dibatalkan</div>
                    </div>
                    <a class="link-arrow" style="font-size:.88rem" href="{{ route('admin.reports') }}">Laporan <i class="ph-bold ph-arrow-right"></i></a>
                </div>
                <div class="card-fm__body">
                    <x-bar-chart :daily="$daily" />
                    <div class="chart-legend mt-3">
                        <span>Omzet per hari</span>
                        <span class="today">Hari ini (masih berjalan)</span>
                    </div>
                </div>
            </div>
        </div>

        {{-- Stok menipis --}}
        <div class="col-xl-4">
            <div class="card-fm h-100">
                <div class="card-fm__head">
                    <h3>Perlu diisi ulang</h3>
                    <a class="link-arrow" style="font-size:.88rem" href="{{ route('admin.stock.index') }}">Stok <i class="ph-bold ph-arrow-right"></i></a>
                </div>
                <div class="card-fm__body py-2">
                    @forelse ($lowStock as $product)
                        <div class="line-item py-2">
                            <x-pack :product="$product" size="mini" />
                            <div class="line-item__info">
                                <a class="line-item__name" style="font-size:.93rem" href="{{ route('admin.products.edit', $product) }}">{{ $product->name }}</a>
                                <div class="line-item__meta">{{ $product->category->name }}</div>
                            </div>
                            <span @class(['stock-pill', 'stock-pill--out' => $product->stock === 0, 'stock-pill--low' => $product->stock > 0])>
                                {{ $product->stock === 0 ? 'habis' : 'sisa '.$product->stock }}
                            </span>
                        </div>
                    @empty
                        <p class="muted text-center py-4 mb-0">Tidak ada stok yang menipis.</p>
                    @endforelse
                </div>
            </div>
        </div>
    </div>

    <div class="row g-3">
        {{-- Pesanan terbaru --}}
        <div class="col-xl-8">
            <div class="card-fm">
                <div class="card-fm__head">
                    <h3>Pesanan terbaru</h3>
                    <a class="link-arrow" style="font-size:.88rem" href="{{ route('admin.orders.index') }}">Semua pesanan <i class="ph-bold ph-arrow-right"></i></a>
                </div>
                <div class="table-wrap">
                    <table class="table-fm">
                        <thead>
                            <tr><th>Pesanan</th><th>Customer</th><th>Waktu</th><th class="num">Total</th><th>Status</th></tr>
                        </thead>
                        <tbody>
                            @forelse ($recentOrders as $order)
                                <tr>
                                    <td><a class="row-link" href="{{ route('admin.orders.show', $order) }}">#{{ $order->code }}</a></td>
                                    <td>{{ $order->customer_name }}</td>
                                    <td class="mono muted" style="font-size:.78rem">{{ $order->created_at->diffForHumans() }}</td>
                                    <td class="num fw-semibold">{{ rupiah($order->total) }}</td>
                                    <td><x-status :status="$order->status" /></td>
                                </tr>
                            @empty
                                <tr><td colspan="5" class="text-center muted py-4">Belum ada pesanan.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        {{-- Produk terlaris --}}
        <div class="col-xl-4">
            <div class="card-fm h-100">
                <div class="card-fm__head">
                    <h3>Terlaris 30 hari</h3>
                    <span class="tag">kemasan</span>
                </div>
                <div class="card-fm__body">
                    @forelse ($topProducts as $index => $row)
                        <div class="hbar" style="grid-template-columns:22px minmax(0,1.3fr) 1fr auto">
                            <span class="rank">{{ str_pad($index + 1, 2, '0', STR_PAD_LEFT) }}</span>
                            <span class="hbar__name" title="{{ $row['name'] }}">{{ $row['name'] }}</span>
                            <span class="hbar__track"><span style="width:{{ round($row['qty'] / $topMax * 100) }}%"></span></span>
                            <span class="hbar__val" style="min-width:28px">{{ $row['qty'] }}</span>
                        </div>
                    @empty
                        <p class="muted text-center py-4 mb-0">Belum ada penjualan.</p>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
@endsection
