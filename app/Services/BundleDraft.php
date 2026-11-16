<?php

namespace App\Services;

use App\Models\Product;
use Illuminate\Support\Collection;

/**
 * Bundle yang sedang diracik customer, disimpan di session.
 * Setiap perubahan isi dihitung ulang di server lewat summary().
 */
class BundleDraft
{
    private const KEY = 'bundle.draft';

    public const MAX_QTY = 20;

    public function exists(): bool
    {
        return session()->has(self::KEY);
    }

    public function get(): array
    {
        return session(self::KEY, [
            'name' => 'Bundle Racikanku',
            'need' => null,
            'people' => null,
            'budget' => null,
            'meals' => null,
            'items' => [],
        ]);
    }

    public function start(string $name, array $params, array $items): void
    {
        session([self::KEY => [
            'name' => $name,
            'need' => $params['need'] ?? null,
            'people' => $params['people'] ?? null,
            'budget' => $params['budget'] ?? null,
            'meals' => $params['meals'] ?? null,
            'items' => $items,
        ]]);
    }

    /**
     * Simpan isi baru. Qty dipotong ke stok yang tersedia.
     *
     * @param  array<int, int>  $items  product_id => qty
     */
    public function setItems(array $items): void
    {
        $draft = $this->get();
        $products = Product::available()->whereIn('id', array_keys($items))->get()->keyBy('id');

        $draft['items'] = collect($items)
            ->filter(fn ($qty, $id) => $products->has($id) && $qty > 0)
            ->map(fn ($qty, $id) => (int) min($qty, $products[$id]->stock, self::MAX_QTY))
            ->all();

        session([self::KEY => $draft]);
    }

    public function forget(): void
    {
        session()->forget(self::KEY);
    }

    /** Ringkasan lengkap untuk halaman racik dan respons JSON. */
    public function summary(): array
    {
        $draft = $this->get();
        // Produk yang sudah habis/nonaktif otomatis keluar dari racikan.
        $products = Product::with('category')->available()->whereIn('id', array_keys($draft['items']))->get()->keyBy('id');

        /** @var Collection $lines */
        $lines = collect($draft['items'])
            ->filter(fn ($qty, $id) => $products->has($id))
            ->map(function ($qty, $id) use ($products, $draft) {
                $p = $products[$id];

                return [
                    'id' => $p->id,
                    'name' => $p->name,
                    'category' => $p->category->name,
                    'price' => $p->price,
                    'qty' => (int) $qty,
                    'subtotal' => $p->price * $qty,
                    'servings' => $p->effectiveServings($draft['need']) * $qty,
                    'stock' => $p->stock,
                    'weight' => $p->weightLabel(),
                ];
            })
            ->values();

        $total = $lines->sum('subtotal');
        $servings = $lines->sum('servings');
        $budget = $draft['budget'] ? config("frozemate.budgets.{$draft['budget']}") : null;
        $target = ($draft['people'] && $draft['meals']) ? $draft['people'] * $draft['meals'] : null;

        return [
            'name' => $draft['name'],
            'lines' => $lines->all(),
            'count' => $lines->sum('qty'),
            'total' => $total,
            'total_label' => rupiah($total),
            'servings' => (int) floor($servings),
            'meals_covered' => $draft['people'] ? (int) floor($servings / $draft['people']) : null,
            'target' => $target,
            'coverage' => $target ? min(100, (int) floor($servings / $target * 100)) : null,
            'budget' => $budget ? $this->budgetState($budget, $total) : null,
            'need' => $draft['need'] ? config("frozemate.needs.{$draft['need']}.label") : null,
            'people' => $draft['people'] ? config("frozemate.people.{$draft['people']}") : null,
            'meals' => $draft['meals'],
        ];
    }

    private function budgetState(array $budget, int $total): array
    {
        $max = $budget['max'];

        if ($max === null) {
            return [
                'label' => $budget['long'],
                'max' => null,
                'state' => 'ok',
                'percent' => min(100, (int) round($total / max(1, $budget['min']) * 100)),
                'message' => $total >= $budget['min']
                    ? 'Sesuai rentang budgetmu.'
                    : 'Masih '.rupiah($budget['min'] - $total).' di bawah rentang budgetmu.',
            ];
        }

        $over = $total > $max;

        return [
            'label' => $budget['long'],
            'max' => $max,
            'state' => $over ? 'over' : 'ok',
            'percent' => min(100, (int) round($total / $max * 100)),
            'message' => $over
                ? 'Lewat '.rupiah($total - $max).' dari batas budget.'
                : 'Sisa '.rupiah($max - $total).' dari batas budget.',
        ];
    }
}
