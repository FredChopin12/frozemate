@extends('layouts.admin')

@section('title', 'Laporan penjualan')
@section('eyebrow', 'Ringkasan')
@section('heading', 'Laporan penjualan')

@section('actions')
    <div class="chip-row">
        @foreach ([7 => '7 hari', 14 => '14 hari', 30 => '30 hari'] as $value => $label)
            <a @class(['chip', 'is-active' => $days === $value]) href="{{ route('admin.reports', ['rentang' => $value]) }}">{{ $label }}</a>
        @endforeach
    </div>
@endsection

@php
    $topMax = max(1, $topProducts->max('revenue'));
    $catMax = max(1, $categories->max('revenue'));
@endphp

@section('content')
    <div class="row g-3 mb-3">
        <div class="col-sm-6 col-xl-3">
            <div class="stat stat--hero">
                <div class="stat__label">Omzet {{ $days }} hari <i class="ph ph-coins"></i></div>
                <div class="stat__value">{{ rupiah($totals['revenue']) }}</div>
                <div class="stat__foot">
                    @if ($totals['change'] !== null)
                        <span @class(['delta', 'delta--up' => $totals['change'] >= 0, 'delta--down' => $totals['change'] < 0])>
                            <i class="ph-bold {{ $totals['change'] >= 0 ? 'ph-trend-up' : 'ph-trend-down' }}"></i>{{ abs($totals['change']) }}%
                        </span>
                        vs {{ $days }} hari sebelumnya
                    @else
                        belum ada pembanding
                    @endif
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="stat">
                <div class="stat__label">Pesanan <i class="ph ph-receipt"></i></div>
                <div class="stat__value">{{ $totals['orders'] }}</div>
                <div class="stat__foot">tidak termasuk yang dibatalkan</div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="stat">
                <div class="stat__label">Rata-rata per pesanan <i class="ph ph-scales"></i></div>
                <div class="stat__value">{{ rupiah($totals['average']) }}</div>
                <div class="stat__foot">nilai belanja sekali checkout</div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="stat">
                <div class="stat__label">Kemasan terjual <i class="ph ph-package"></i></div>
                <div class="stat__value">{{ number_format($totals['items'], 0, ',', '.') }}</div>
                <div class="stat__foot">dari semua pesanan periode ini</div>
            </div>
        </div>
    </div>

    <div class="card-fm mb-3">
        <div class="card-fm__head">
            <div>
                <h3>Omzet per hari</h3>
                <div class="mono muted mt-1" style="font-size:.74rem">Arahkan kursor ke batang untuk melihat angkanya</div>
            </div>
            <div class="chart-legend">
                <span>Omzet per hari</span>
                <span class="today">Hari ini</span>
            </div>
        </div>
        <div class="card-fm__body">
            <x-bar-chart :daily="$daily" />

            <details class="mt-3">
                <summary class="mono" style="font-size:.76rem;color:var(--glacier);cursor:pointer">Lihat sebagai tabel</summary>
                <div class="table-wrap mt-2" style="border:1.5px solid var(--line);border-radius:var(--r-md)">
                    <table class="table-fm">
                        <thead><tr><th>Tanggal</th><th class="num">Pesanan</th><th class="num">Omzet</th></tr></thead>
                        <tbody>
                            @foreach ($daily->reverse() as $day)
                                <tr>
                                    <td>{{ $day['date']->translatedFormat('l, d M Y') }}</td>
                                    <td class="num mono">{{ $day['orders'] }}</td>
                                    <td class="num fw-semibold">{{ rupiah($day['revenue']) }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </details>
        </div>
    </div>

    <div class="row g-3">
        <div class="col-xl-7">
            <div class="card-fm h-100">
                <div class="card-fm__head"><h3>Produk terlaris</h3><span class="tag">berdasarkan omzet</span></div>
                <div class="card-fm__body">
                    @forelse ($topProducts as $index => $row)
                        <div class="hbar" style="grid-template-columns:22px minmax(0,1.2fr) 1fr auto">
                            <span class="rank">{{ str_pad($index + 1, 2, '0', STR_PAD_LEFT) }}</span>
                            <span class="hbar__name" title="{{ $row['name'] }}">{{ $row['name'] }}</span>
                            <span class="hbar__track"><span style="width:{{ round($row['revenue'] / $topMax * 100) }}%"></span></span>
                            <span class="hbar__val">{{ rupiah($row['revenue']) }} · {{ $row['qty'] }}×</span>
                        </div>
                    @empty
                        <p class="muted text-center py-4 mb-0">Belum ada penjualan pada periode ini.</p>
                    @endforelse
                </div>
            </div>
        </div>

        <div class="col-xl-5">
            <div class="card-fm mb-3">
                <div class="card-fm__head"><h3>Omzet per kategori</h3></div>
                <div class="card-fm__body">
                    @forelse ($categories as $row)
                        <div class="hbar">
                            <span class="hbar__name" title="{{ $row['name'] }}">{{ $row['name'] }}</span>
                            <span class="hbar__track"><span style="width:{{ round($row['revenue'] / $catMax * 100) }}%"></span></span>
                            <span class="hbar__val" style="min-width:44px">{{ $row['share'] }}%</span>
                        </div>
                    @empty
                        <p class="muted text-center py-4 mb-0">Belum ada data.</p>
                    @endforelse
                </div>
            </div>

            <div class="card-fm">
                <div class="card-fm__head"><h3>Bundel vs satuan</h3><span class="tag">Smart Bundle</span></div>
                <div class="card-fm__body">
                    @if ($bundleShare['bundle'] + $bundleShare['single'] > 0)
                        <div class="split-bar mb-3" role="img" aria-label="{{ $bundleShare['percent'] }}% omzet berasal dari bundel">
                            <span class="a" style="width:{{ $bundleShare['percent'] }}%"></span>
                            <span class="b" style="width:{{ 100 - $bundleShare['percent'] }}%"></span>
                        </div>
                        <div class="d-flex justify-content-between gap-3 flex-wrap">
                            <div>
                                <div class="mono muted" style="font-size:.7rem"><span style="display:inline-block;width:10px;height:10px;border-radius:3px;background:var(--ember)"></span> DARI BUNDEL · {{ $bundleShare['percent'] }}%</div>
                                <div class="price" style="font-size:1.25rem">{{ rupiah($bundleShare['bundle']) }}</div>
                            </div>
                            <div class="text-end">
                                <div class="mono muted" style="font-size:.7rem"><span style="display:inline-block;width:10px;height:10px;border-radius:3px;background:var(--glacier)"></span> PRODUK SATUAN · {{ 100 - $bundleShare['percent'] }}%</div>
                                <div class="price" style="font-size:1.25rem">{{ rupiah($bundleShare['single']) }}</div>
                            </div>
                        </div>
                    @else
                        <p class="muted text-center py-3 mb-0">Belum ada data.</p>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection
