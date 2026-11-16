<?php

namespace App\Http\Controllers\Admin;

use App\Enums\OrderStatus;
use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class OrderController extends Controller
{
    public function index(Request $request)
    {
        $status = OrderStatus::tryFrom((string) $request->input('status'));

        $orders = Order::withCount('items')
            ->when($status, fn ($query) => $query->where('status', $status->value))
            ->when($request->string('q')->trim()->value(), function ($query, $q) {
                $query->where(fn ($w) => $w
                    ->where('code', 'like', '%'.ltrim($q, '#').'%')
                    ->orWhere('customer_name', 'like', "%$q%")
                    ->orWhere('phone', 'like', "%$q%"));
            })
            ->latest()
            ->paginate(12)
            ->withQueryString();

        return view('admin.orders.index', [
            'orders' => $orders,
            'status' => $status,
            'counts' => Order::selectRaw('status, count(*) as total')->groupBy('status')->pluck('total', 'status'),
        ]);
    }

    public function show(Order $order)
    {
        $order->load(['items.product.category', 'user']);

        return view('admin.orders.show', [
            'order' => $order,
            'groups' => $order->items->groupBy(fn ($item) => $item->bundle_name ?? '')->sortKeysDesc(),
        ]);
    }

    public function update(Request $request, Order $order)
    {
        $data = $request->validate([
            'action' => ['required', Rule::in(['advance', 'cancel'])],
        ]);

        if ($data['action'] === 'advance') {
            $next = $order->status->next();

            if (! $next) {
                return back()->with('error', 'Status pesanan ini sudah final.');
            }

            $order->update(['status' => $next]);

            return back()->with('success', "Pesanan #{$order->code} → {$next->label()}.");
        }

        if (! $order->status->canCancel()) {
            return back()->with('error', 'Pesanan ini tidak bisa dibatalkan lagi.');
        }

        // Pembatalan mengembalikan stok produk yang masih ada di katalog.
        DB::transaction(function () use ($order) {
            foreach ($order->items as $item) {
                if ($item->product_id) {
                    Product::whereKey($item->product_id)->increment('stock', $item->quantity);
                }
            }

            $order->update(['status' => OrderStatus::Cancelled]);
        });

        return back()->with('info', "Pesanan #{$order->code} dibatalkan dan stok dikembalikan.");
    }
}
