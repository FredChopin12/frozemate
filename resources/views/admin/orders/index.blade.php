@extends('layouts.admin')

@use('App\Enums\OrderStatus')

@section('title', 'Pesanan')
@section('eyebrow', 'Operasional')
@section('heading', 'Pesanan')

@section('content')
    <div class="card-fm">
        <div class="card-fm__head flex-wrap">
            <div class="chip-scroll">
                <a @class(['chip', 'is-active' => ! $status]) href="{{ route('admin.orders.index', array_filter(['q' => request('q')])) }}">
                    Semua <span class="count">{{ $counts->sum() }}</span>
                </a>
                @foreach (OrderStatus::cases() as $case)
                    <a @class(['chip', 'is-active' => $status === $case]) href="{{ route('admin.orders.index', array_filter(['status' => $case->value, 'q' => request('q')])) }}">
                        {{ $case->label() }} <span class="count">{{ $counts[$case->value] ?? 0 }}</span>
                    </a>
                @endforeach
            </div>
            <form method="GET" class="input-affix input-affix--icon" style="min-width:240px">
                @if ($status) <input type="hidden" name="status" value="{{ $status->value }}"> @endif
                <i class="affix ph ph-magnifying-glass"></i>
                <input type="search" class="form-control" style="height:42px;padding-top:0;padding-bottom:0" name="q" value="{{ request('q') }}" placeholder="Kode, nama, atau telepon…" aria-label="Cari pesanan">
            </form>
        </div>

        <div class="table-wrap">
            <table class="table-fm">
                <thead>
                    <tr>
                        <th>Pesanan</th>
                        <th>Customer</th>
                        <th>Pembayaran</th>
                        <th class="num">Total</th>
                        <th>Status</th>
                        <th class="num">Langkah berikutnya</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($orders as $order)
                        <tr>
                            <td>
                                <a class="row-link" href="{{ route('admin.orders.show', $order) }}">#{{ $order->code }}</a>
                                <div class="mono muted" style="font-size:.7rem">{{ $order->created_at->translatedFormat('d M, H.i') }} · {{ $order->items_count }} baris</div>
                            </td>
                            <td>
                                {{ $order->customer_name }}
                                <div class="mono muted" style="font-size:.7rem">{{ $order->phone }}</div>
                            </td>
                            <td style="font-size:.88rem">{{ $order->paymentLabel() }}</td>
                            <td class="num fw-semibold">{{ rupiah($order->total) }}</td>
                            <td><x-status :status="$order->status" /></td>
                            <td class="num">
                                @if ($order->status->next())
                                    <form method="POST" action="{{ route('admin.orders.update', $order) }}" class="d-inline">
                                        @csrf @method('PATCH')
                                        <button class="btn-fm btn-fm--sm" type="submit" name="action" value="advance">
                                            {{ $order->status->nextActionLabel() }} <i class="ph-bold ph-arrow-right"></i>
                                        </button>
                                    </form>
                                @else
                                    <a class="btn-fm btn-fm--ghost btn-fm--sm" href="{{ route('admin.orders.show', $order) }}">Detail</a>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="text-center muted py-5">Tidak ada pesanan di tampilan ini.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{ $orders->links() }}
@endsection
