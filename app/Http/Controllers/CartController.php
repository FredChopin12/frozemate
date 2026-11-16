<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Services\CartService;
use Illuminate\Http\Request;

class CartController extends Controller
{
    public function __construct(private CartService $cart) {}

    public function index()
    {
        return view('cart.index', [
            'groups' => $this->cart->groups(),
            'total' => $this->cart->total(),
            'count' => $this->cart->count(),
            'issues' => $this->cart->stockIssues(),
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'product_id' => ['required', 'integer', 'exists:products,id'],
            'qty' => ['nullable', 'integer', 'min:1', 'max:50'],
        ]);

        $product = Product::findOrFail($data['product_id']);
        $added = $this->cart->add($product, $data['qty'] ?? 1);

        if ($added === 0) {
            return back()->with('error', "Stok {$product->name} tidak cukup.");
        }

        return back()->with('success', "{$added}× {$product->name} masuk keranjang.");
    }

    public function update(Request $request, string $key)
    {
        $data = $request->validate([
            'qty' => ['required', 'integer', 'min:0', 'max:50'],
        ]);

        $this->cart->update($key, $data['qty']);

        return back();
    }

    public function destroy(string $key)
    {
        $this->cart->remove($key);

        return back()->with('info', 'Produk dihapus dari keranjang.');
    }

    public function clear()
    {
        $this->cart->clear();

        return back()->with('info', 'Keranjang dikosongkan.');
    }
}
