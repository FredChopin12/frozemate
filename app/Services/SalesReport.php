<?php

namespace App\Services;

use App\Models\Order;
use App\Models\OrderItem;
use Carbon\CarbonImmutable;
use Illuminate\Support\Collection;

/**
 * Ringkasan penjualan untuk dashboard dan halaman laporan.
 * Pesanan yang dibatalkan tidak dihitung.
 */
class SalesReport
{
    /** Omzet & jumlah pesanan per hari, termasuk hari tanpa penjualan. */
    public function daily(int $days): Collection
    {
        $from = CarbonImmutable::today()->subDays($days - 1);

        $orders = Order::counted()->where('created_at', '>=', $from)->get(['total', 'created_at'])
            ->groupBy(fn (Order $order) => $order->created_at->toDateString());

        return collect(range(0, $days - 1))->map(function (int $offset) use ($from, $orders) {
            $date = $from->addDays($offset);
            $bucket = $orders->get($date->toDateString(), collect());

            return [
                'date' => $date,
                'revenue' => (int) $bucket->sum('total'),
                'orders' => $bucket->count(),
            ];
        });
    }

    /** Angka utama untuk satu periode + perbandingan dengan periode sebelumnya. */
    public function totals(int $days): array
    {
        $from = CarbonImmutable::today()->subDays($days - 1);
        $previousFrom = $from->subDays($days);

        $current = Order::counted()->where('created_at', '>=', $from)->get(['id', 'total']);
        $previous = Order::counted()->whereBetween('created_at', [$previousFrom, $from])->sum('total');

        $revenue = (int) $current->sum('total');

        return [
            'revenue' => $revenue,
            'orders' => $current->count(),
            'average' => $current->count() ? (int) round($revenue / $current->count()) : 0,
            'items' => (int) OrderItem::whereIn('order_id', $current->pluck('id'))->sum('quantity'),
            'previous' => (int) $previous,
            'change' => $previous > 0 ? (int) round(($revenue - $previous) / $previous * 100) : null,
        ];
    }

    /** @param  'qty'|'revenue'  $by */
    public function topProducts(int $days, int $limit = 5, string $by = 'qty'): Collection
    {
        return $this->items($days)
            ->groupBy('product_name')
            ->map(fn (Collection $items, string $name) => [
                'name' => $name,
                'qty' => (int) $items->sum('quantity'),
                'revenue' => (int) $items->sum('subtotal'),
            ])
            ->sortByDesc($by)
            ->take($limit)
            ->values();
    }

    public function byCategory(int $days): Collection
    {
        $rows = $this->items($days)
            ->groupBy(fn (OrderItem $item) => $item->product?->category?->name ?? 'Produk terhapus')
            ->map(fn (Collection $items, string $name) => [
                'name' => $name,
                'tone' => $items->first()->product?->category?->tone ?? 'blue',
                'qty' => (int) $items->sum('quantity'),
                'revenue' => (int) $items->sum('subtotal'),
            ])
            ->sortByDesc('revenue')
            ->values();

        $total = max(1, $rows->sum('revenue'));

        return $rows->map(fn (array $row) => $row + ['share' => (int) round($row['revenue'] / $total * 100)]);
    }

    /** Berapa banyak item terjual sebagai bagian dari bundle vs satuan. */
    public function bundleShare(int $days): array
    {
        $items = $this->items($days);
        $total = max(1, (int) $items->sum('subtotal'));
        $bundle = (int) $items->whereNotNull('bundle_name')->sum('subtotal');

        return [
            'bundle' => $bundle,
            'single' => (int) $items->sum('subtotal') - $bundle,
            'percent' => (int) round($bundle / $total * 100),
        ];
    }

    private ?Collection $itemsCache = null;

    private ?int $itemsCacheDays = null;

    private function items(int $days): Collection
    {
        if ($this->itemsCache && $this->itemsCacheDays === $days) {
            return $this->itemsCache;
        }

        $from = CarbonImmutable::today()->subDays($days - 1);

        $this->itemsCacheDays = $days;

        return $this->itemsCache = OrderItem::with('product.category')
            ->whereHas('order', fn ($query) => $query->counted()->where('created_at', '>=', $from))
            ->get();
    }
}
