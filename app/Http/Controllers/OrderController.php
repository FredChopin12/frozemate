<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    public function index(Request $request)
    {
        return view('orders.index', [
            'orders' => $request->user()->orders()->with('items')->latest()->paginate(8),
        ]);
    }

    public function show(Request $request, Order $order)
    {
        // Customer hanya boleh melihat pesanannya sendiri.
        abort_unless($order->user_id === $request->user()->id, 404);

        $order->load('items.product.category');

        return view('orders.show', [
            'order' => $order,
            'groups' => $order->items->groupBy(fn ($item) => $item->bundle_name ?? '')->sortKeysDesc(),
            'placed' => (bool) session('placed'),
        ]);
    }
}
