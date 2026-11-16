<?php

namespace App\Services;

use App\Models\Product;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

/**
 * Keranjang belanja berbasis session.
 *
 * Bentuk data di session:
 *   cart.lines = [ '12-solo' => ['product_id' => 12, 'qty' => 2, 'bundle' => null], ... ]
 * Produk yang berasal dari bundle disimpan di baris terpisah supaya tetap
 * tampil berkelompok di keranjang dan di pesanan.
 */
class CartService
{
    private const KEY = 'cart.lines';

    private ?Collection $cache = null;

    private function raw(): array
    {
        return session(self::KEY, []);
    }

    private function store(array $lines): void
    {
        session([self::KEY => $lines]);
        $this->cache = null;
    }

    private function key(int $productId, ?string $bundle): string
    {
        return $productId.'-'.($bundle ? Str::slug($bundle) : 'solo');
    }

    /** Jumlah produk ini di seluruh baris keranjang. */
    private function qtyInCart(int $productId, ?string $exceptKey = null): int
    {
        return collect($this->raw())
            ->reject(fn ($line, $key) => $key === $exceptKey)
            ->where('product_id', $productId)
            ->sum('qty');
    }

    /**
     * Tambah produk. Mengembalikan jumlah yang benar-benar masuk
     * (bisa lebih kecil dari permintaan kalau stok tidak cukup).
     */
    public function add(Product $product, int $qty = 1, ?string $bundle = null): int
    {
        if (! $product->is_active) {
            return 0;
        }

        $room = max(0, $product->stock - $this->qtyInCart($product->id));
        $qty = min(max(1, $qty), $room);

        if ($qty === 0) {
            return 0;
        }

        $lines = $this->raw();
        $key = $this->key($product->id, $bundle);

        $lines[$key] = [
            'product_id' => $product->id,
            'qty' => ($lines[$key]['qty'] ?? 0) + $qty,
            'bundle' => $bundle,
        ];

        $this->store($lines);

        return $qty;
    }

    /**
     * Masukkan satu bundle sekaligus.
     *
     * @param  array<int, int>  $items  product_id => qty
     * @return array{added: int, short: array<int, string>}
     */
    public function addBundle(string $name, array $items): array
    {
        $products = Product::whereIn('id', array_keys($items))->get()->keyBy('id');
        $added = 0;
        $short = [];

        foreach ($items as $id => $qty) {
            $product = $products->get($id);
            $in = $product ? $this->add($product, (int) $qty, $name) : 0;
            $added += $in;

            if ($product && $in < $qty) {
                $short[] = $product->name;
            }
        }

        return ['added' => $added, 'short' => $short];
    }

    public function update(string $key, int $qty): void
    {
        $lines = $this->raw();

        if (! isset($lines[$key])) {
            return;
        }

        if ($qty <= 0) {
            unset($lines[$key]);
            $this->store($lines);

            return;
        }

        $product = Product::find($lines[$key]['product_id']);
        $room = $product ? max(0, $product->stock - $this->qtyInCart($product->id, $key)) : 0;

        $lines[$key]['qty'] = min($qty, $room);

        if ($lines[$key]['qty'] <= 0) {
            unset($lines[$key]);
        }

        $this->store($lines);
    }

    public function remove(string $key): void
    {
        $lines = $this->raw();
        unset($lines[$key]);
        $this->store($lines);
    }

    public function clear(): void
    {
        session()->forget(self::KEY);
        $this->cache = null;
    }

    /**
     * Baris keranjang lengkap dengan data produk terbaru dari database.
     *
     * @return Collection<int, object{key: string, product: Product, qty: int, bundle: ?string, subtotal: int}>
     */
    public function lines(): Collection
    {
        if ($this->cache) {
            return $this->cache;
        }

        $raw = collect($this->raw());

        if ($raw->isEmpty()) {
            return $this->cache = collect();
        }

        $products = Product::with('category')
            ->whereIn('id', $raw->pluck('product_id')->unique())
            ->get()
            ->keyBy('id');

        return $this->cache = $raw
            ->map(function (array $line, string $key) use ($products) {
                $product = $products->get($line['product_id']);

                if (! $product || ! $product->is_active) {
                    return null;
                }

                return (object) [
                    'key' => $key,
                    'product' => $product,
                    'qty' => (int) $line['qty'],
                    'bundle' => $line['bundle'] ?? null,
                    'subtotal' => $product->price * $line['qty'],
                ];
            })
            ->filter()
            ->values();
    }

    /** Baris dikelompokkan per bundle; produk satuan memakai kunci ''. */
    public function groups(): Collection
    {
        return $this->lines()
            ->groupBy(fn ($line) => $line->bundle ?? '')
            ->sortKeysDesc();
    }

    public function count(): int
    {
        return (int) collect($this->raw())->sum('qty');
    }

    public function total(): int
    {
        return (int) $this->lines()->sum('subtotal');
    }

    public function isEmpty(): bool
    {
        return $this->lines()->isEmpty();
    }

    /** Nama produk yang jumlahnya di keranjang melebihi stok saat ini. */
    public function stockIssues(): Collection
    {
        return $this->lines()
            ->groupBy(fn ($line) => $line->product->id)
            ->filter(fn (Collection $lines) => $lines->sum('qty') > $lines->first()->product->stock)
            ->map(fn (Collection $lines) => $lines->first()->product->name)
            ->values();
    }
}
