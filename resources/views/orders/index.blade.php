@extends('layouts.app')

@section('title', 'Pesanan saya')

@section('content')
    <div class="container">
        <div class="page-head">
            <nav class="crumbs" aria-label="Breadcrumb"><a href="{{ route('home') }}">Beranda</a> / <span>Pesanan saya</span></nav>
            <h1>Pesanan saya</h1>
        </div>

        @if ($orders->isEmpty())
            <div class="empty">
                <div class="empty__icon"><i class="ph ph-receipt"></i></div>
                <h3>Belum ada pesanan</h3>
                <p>Pesanan pertamamu akan muncul di sini, lengkap dengan statusnya.</p>
                <a class="btn-fm btn-fm--primary" href="{{ route('bundle.index') }}">Racik bundel pertama <i class="ph-bold ph-arrow-right"></i></a>
            </div>
        @else
            <div class="d-grid gap-3" style="max-width:860px">
                @foreach ($orders as $order)
                    <a class="card-fm text-decoration-none d-block" href="{{ route('orders.show', $order) }}" style="color:inherit">
                        <div class="card-fm__body d-flex flex-wrap align-items-center gap-3">
                            <div class="flex-grow-1" style="min-width:220px">
                                <div class="d-flex align-items-center gap-2 flex-wrap">
                                    <span class="display" style="font-size:1.15rem">#{{ $order->code }}</span>
                                    <x-status :status="$order->status" />
                                </div>
                                <div class="mono muted mt-1" style="font-size:.74rem">
                                    {{ $order->created_at->translatedFormat('d M Y, H.i') }} · {{ $order->itemCount() }} kemasan · {{ $order->paymentLabel() }}
                                </div>
                                <div class="mt-2" style="font-size:.9rem;color:var(--ink-2)">
                                    {{ $order->items->take(3)->pluck('product_name')->implode(', ') }}@if ($order->items->count() > 3), +{{ $order->items->count() - 3 }} lainnya @endif
                                </div>
                            </div>
                            <div class="text-end ms-auto">
                                <div class="price" style="font-size:1.35rem">{{ rupiah($order->total) }}</div>
                                <span class="mono" style="font-size:.74rem;color:var(--glacier)">Lihat detail →</span>
                            </div>
                        </div>
                    </a>
                @endforeach
            </div>

            {{ $orders->links() }}
        @endif
    </div>
@endsection
