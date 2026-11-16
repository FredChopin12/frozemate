@extends('layouts.admin')

@use('App\Enums\OrderStatus')

@php $cancelled = $order->status === OrderStatus::Cancelled; @endphp

@section('title', 'Pesanan #'.$order->code)
@section('eyebrow', 'Operasional / Pesanan')
@section('heading', '#'.$order->code)

@section('actions')
    <a class="btn-fm btn-fm--ghost" href="{{ route('admin.orders.index') }}"><i class="ph-bold ph-arrow-left"></i> Semua pesanan</a>
@endsection

@section('content')
    <div class="row g-3">
        <div class="col-xl-8">
            {{-- Status & aksi --}}
            <div class="card-fm mb-3">
                <div class="card-fm__head">
                    <div class="d-flex align-items-center gap-2 flex-wrap">
                        <h3>Status</h3>
                        <x-status :status="$order->status" />
                    </div>
                    <span class="mono muted" style="font-size:.74rem">Dibuat {{ $order->created_at->translatedFormat('d M Y, H.i') }}</span>
                </div>
                <div class="card-fm__body">
                    @if ($cancelled)
                        <div class="notice notice--danger"><i class="ph-fill ph-x-circle"></i><div><strong>Pesanan dibatalkan.</strong> Stok produknya sudah dikembalikan.</div></div>
                    @else
                        <div class="tracker mb-4">
                            @foreach (OrderStatus::flow() as $index => $step)
                                <div @class(['tracker__step', 'is-done' => $index < $order->status->step() || $order->status === OrderStatus::Completed, 'is-current' => $index === $order->status->step() && $order->status !== OrderStatus::Completed])>
                                    <span class="node"><i class="ph-bold ph-check"></i></span>
                                    <strong>{{ $step->label() }}</strong>
                                </div>
                            @endforeach
                        </div>

                        <div class="d-flex gap-2 flex-wrap align-items-center">
                            @if ($order->status->next())
                                <form method="POST" action="{{ route('admin.orders.update', $order) }}">
                                    @csrf @method('PATCH')
                                    <button class="btn-fm btn-fm--primary" type="submit" name="action" value="advance">
                                        {{ $order->status->nextActionLabel() }} <i class="ph-bold ph-arrow-right"></i>
                                    </button>
                                </form>
                                <span class="mono muted" style="font-size:.76rem">→ status menjadi “{{ $order->status->next()->label() }}”</span>
                            @else
                                <span class="muted">Pesanan sudah selesai. Tidak ada langkah lagi.</span>
                            @endif

                            @if ($order->status->canCancel())
                                <form method="POST" action="{{ route('admin.orders.update', $order) }}" class="ms-auto"
                                      data-confirm="Batalkan pesanan #{{ $order->code }}? Stok akan dikembalikan dan tindakan ini tidak bisa diurungkan.">
                                    @csrf @method('PATCH')
                                    <button class="btn-fm btn-fm--danger" type="submit" name="action" value="cancel"><i class="ph ph-x-circle"></i> Batalkan pesanan</button>
                                </form>
                            @endif
                        </div>
                    @endif
                </div>
            </div>

            {{-- Isi pesanan --}}
            <div class="card-fm">
                <div class="card-fm__head"><h3>Isi pesanan</h3><span class="result-count">{{ $order->items->sum('quantity') }} kemasan</span></div>
                <div class="table-wrap">
                    <table class="table-fm">
                        <thead><tr><th>Produk</th><th class="num">Harga</th><th class="num">Qty</th><th class="num">Subtotal</th></tr></thead>
                        <tbody>
                            @foreach ($groups as $bundleName => $items)
                                <tr>
                                    <td colspan="4" style="background:var(--ice);padding-block:.45rem">
                                        <span class="mono" style="font-size:.7rem;letter-spacing:.07em;text-transform:uppercase;color:var(--ink-2)">
                                            {{ $bundleName !== '' ? 'Bundel · '.$bundleName : 'Produk satuan' }}
                                        </span>
                                    </td>
                                </tr>
                                @foreach ($items as $item)
                                    <tr>
                                        <td>
                                            <div class="cell-product">
                                                <x-pack :product="$item->product" size="mini" />
                                                <div>
                                                    <span class="fw-semibold">{{ $item->product_name }}</span>
                                                    @unless ($item->product) <small>produk sudah dihapus dari katalog</small> @endunless
                                                </div>
                                            </div>
                                        </td>
                                        <td class="num">{{ rupiah($item->price) }}</td>
                                        <td class="num mono">{{ $item->quantity }}</td>
                                        <td class="num fw-semibold">{{ rupiah($item->subtotal) }}</td>
                                    </tr>
                                @endforeach
                            @endforeach
                            <tr>
                                <td colspan="3" class="num mono" style="font-size:.74rem;letter-spacing:.07em;text-transform:uppercase">Total</td>
                                <td class="num price" style="font-size:1.25rem">{{ rupiah($order->total) }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="col-xl-4">
            <div class="card-fm mb-3">
                <div class="card-fm__head"><h3>Customer</h3></div>
                <div class="card-fm__body">
                    <dl class="dl-fm" style="grid-template-columns:96px 1fr">
                        <dt>Nama</dt><dd>{{ $order->customer_name }}</dd>
                        <dt>Telepon</dt><dd>{{ $order->phone }}</dd>
                        <dt>Alamat</dt><dd>{{ $order->address }}</dd>
                        <dt>Akun</dt><dd>{{ $order->user?->email ?? '—' }}</dd>
                    </dl>
                </div>
            </div>

            <div class="card-fm">
                <div class="card-fm__head"><h3>Pembayaran</h3></div>
                <div class="card-fm__body">
                    <dl class="dl-fm" style="grid-template-columns:96px 1fr">
                        <dt>Metode</dt><dd>{{ $order->paymentLabel() }}</dd>
                        <dt>Total</dt><dd class="fw-semibold">{{ rupiah($order->total) }}</dd>
                        <dt>Catatan</dt><dd>{{ $order->notes ?: '—' }}</dd>
                    </dl>
                </div>
            </div>
        </div>
    </div>
@endsection
