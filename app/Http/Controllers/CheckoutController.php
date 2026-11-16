<?php

namespace App\Http\Controllers;

use App\Http\Requests\CheckoutRequest;
use App\Models\Order;
use App\Models\Product;
use App\Services\CartService;
use Illuminate\Support\Facades\DB;

class CheckoutController extends Controller
{
    public function __construct(private CartService $cart) {}

    public function create()
    {
        if ($this->cart->isEmpty()) {
            return redirect()->route('cart.index')->with('info', 'Keranjangmu masih kosong.');
        }

        if ($this->cart->stockIssues()->isNotEmpty()) {
            return redirect()->route('cart.index');
        }

        return view('checkout.create', [
            'groups' => $this->cart->groups(),
            'total' => $this->cart->total(),
            'count' => $this->cart->count(),
            'user' => auth()->user(),
        ]);
    }

    public function store(CheckoutRequest $request)
    {
        $lines = $this->cart->lines();

        if ($lines->isEmpty()) {
            return redirect()->route('cart.index')->with('info', 'Keranjangmu masih kosong.');
        }

        $data = $request->validated();
        $user = $request->user();

        try {
            $order = DB::transaction(function () use ($lines, $data, $user) {
                // Kunci baris produk supaya stok tidak terjual dua kali.
                $products = Product::whereIn('id', $lines->pluck('product.id')->unique())
                    ->lockForUpdate()->get()->keyBy('id');

                $needed = $lines->groupBy(fn ($line) => $line->product->id)->map->sum('qty');

                foreach ($needed as $productId => $qty) {
                    $product = $products->get($productId);

                    if (! $product || ! $product->is_active || $product->stock < $qty) {
                        throw new \DomainException($product?->name ?? 'Salah satu produk');
                    }
                }

                $order = Order::create([
                    'user_id' => $user->id,
                    'customer_name' => $data['customer_name'],
                    'phone' => $data['phone'],
                    'address' => $data['address'],
                    'payment_method' => $data['payment_method'],
                    'notes' => $data['notes'] ?? null,
                    'total' => $lines->sum(fn ($line) => $products[$line->product->id]->price * $line->qty),
                ]);

                $order->items()->createMany($lines->map(fn ($line) => [
                    'product_id' => $line->product->id,
                    'product_name' => $line->product->name,
                    'bundle_name' => $line->bundle,
                    'price' => $products[$line->product->id]->price,
                    'quantity' => $line->qty,
                    'subtotal' => $products[$line->product->id]->price * $line->qty,
                ])->all());

                foreach ($needed as $productId => $qty) {
                    $products[$productId]->decrement('stock', $qty);
                }

                return $order;
            });
        } catch (\DomainException $e) {
            return redirect()->route('cart.index')
                ->with('error', "Stok {$e->getMessage()} baru saja berubah. Periksa lagi keranjangmu.");
        }

        if ($request->boolean('save_profile')) {
            $user->update(['phone' => $data['phone'], 'address' => $data['address']]);
        }

        $this->cart->clear();

        return redirect()->route('orders.show', $order)->with('placed', true);
    }
}
