<?php

namespace App\Services;

use App\Models\Product;
use Illuminate\Support\Collection;

/**
 * Smart Frozen Food Bundle — rekomendasi berbasis aturan (tanpa AI).
 *
 * Cara kerja singkat:
 *  1. Target porsi = jumlah orang × jumlah kali makan.
 *  2. Porsi lauk dihitung penuh; pendamping/camilan (kentang, kudapan)
 *     dihitung setengah karena tidak mengenyangkan sendirian — kecuali
 *     untuk kebutuhan "Acara" yang memang berisi camilan.
 *  3. Setiap produk diberi skor dari: cocok dengan kebutuhan (tag),
 *     harga per porsi, variasi kategori, dan pas-tidaknya ukuran kemasan.
 *  4. Produk dipilih satu per satu (greedy) sampai porsi tercukupi, tanpa
 *     pernah melewati batas atas budget dan stok.
 *  5. Tiga profil bobot menghasilkan tiga pilihan: Pas, Hemat, dan Variasi.
 */
class BundleRecommender
{
    /**
     * Bobot skor tiap profil.
     * fill     : teruskan belanja sampai masuk rentang budget (bukan hanya cukup porsi)
     * max_same : batas kemasan yang sama dalam satu bundle
     */
    private const PROFILES = [
        'pas' => ['relevance' => 2.0, 'value' => 1.0, 'variety' => 1.3, 'fill' => true, 'max_same' => 2],
        'hemat' => ['relevance' => 0.8, 'value' => 2.6, 'variety' => 0.5, 'fill' => false, 'max_same' => 3],
        'variatif' => ['relevance' => 1.2, 'value' => 0.4, 'variety' => 2.6, 'fill' => true, 'max_same' => 1],
    ];

    /**
     * @return Collection<string, array> bundle per profil, yang utama ('pas') lebih dulu
     */
    public function recommend(string $need, int $people, string $budget, int $meals): Collection
    {
        $needConfig = config("frozemate.needs.$need");
        $budget = config("frozemate.budgets.$budget");
        $target = $people * $meals;

        $pool = Product::with('category')->available()->get();

        if ($pool->isEmpty()) {
            return collect();
        }

        // Siapkan angka yang dipakai berulang saat memberi skor.
        $pool = $pool->map(fn (Product $p) => [
            'product' => $p,
            'servings' => $p->effectiveServings($need),
            'per_serving' => $p->price / max(0.5, $p->effectiveServings($need)),
            'relevance' => in_array($need, $p->tags ?? [], true) ? 1.0 : 0.0,
        ]);

        $cheapest = $pool->min('per_serving');
        $spread = max(1, $pool->max('per_serving') - $cheapest);

        $pool = $pool->map(fn (array $row) => $row + [
            'value' => 1 - (($row['per_serving'] - $cheapest) / $spread),
        ]);

        $min = $budget['min'];
        $max = $budget['max'] ?? $this->openEndedCap($pool, $target, $min);

        return collect(self::PROFILES)
            ->map(fn (array $profile, string $key) => $this->build($pool, $profile, $key, $target, $min, $max) + [
                'key' => $key,
                'name' => $this->name($needConfig['bundle'], $key),
                'tagline' => $this->tagline($key),
                'target' => $target,
                'people' => $people,
                'meals' => $meals,
                'budget_min' => $min,
                'budget_max' => $budget['max'],
            ])
            ->map(fn (array $bundle) => $bundle + [
                'meals_covered' => (int) floor($bundle['servings'] / max(1, $people)),
            ])
            ->filter(fn (array $bundle) => $bundle['items']->isNotEmpty())
            // Buang pilihan yang isinya sama persis dengan pilihan sebelumnya.
            ->unique('signature');
    }

    private function build(Collection $pool, array $profile, string $key, int $target, int $min, int $max): array
    {
        $picked = [];      // product_id => qty
        $usedCategories = [];
        $total = 0;
        $servings = 0.0;
        $minKinds = $this->minimumKinds($key, $target);
        $cheapestPrice = $pool->min(fn (array $row) => $row['product']->price);
        $cheapestServing = $pool->min('per_serving');

        for ($round = 0; $round < 40; $round++) {
            $enoughFood = $servings >= $target;
            $enoughSpend = ! $profile['fill'] || $total >= $min;
            $enoughKinds = count($picked) >= $minKinds;

            if ($enoughFood && $enoughSpend && $enoughKinds) {
                break;
            }

            $state = [
                'picked' => $picked,
                'used' => $usedCategories,
                'remaining' => max(0, $target - $servings),
                'target' => $target,
                'budget_left' => $max - $total,
                'kinds_missing' => max(0, $minKinds - count($picked)),
                'cheapest_price' => $cheapestPrice,
                'cheapest_serving' => $cheapestServing,
            ];

            $best = $pool
                ->filter(function (array $row) use ($picked, $profile, $state) {
                    $p = $row['product'];

                    return $p->price <= $state['budget_left']
                        && ($picked[$p->id] ?? 0) < min($p->stock, $profile['max_same']);
                })
                ->sortBy([
                    fn (array $a, array $b) => $this->score($b, $profile, $state) <=> $this->score($a, $profile, $state),
                    fn (array $a, array $b) => $a['product']->id <=> $b['product']->id,
                ])
                ->first();

            if (! $best) {
                break; // budget atau stok sudah tidak memungkinkan
            }

            $p = $best['product'];
            $picked[$p->id] = ($picked[$p->id] ?? 0) + 1;
            $usedCategories[$p->category_id] = true;
            $total += $p->price;
            $servings += $best['servings'];
        }

        $products = $pool->pluck('product')->keyBy('id');

        $items = collect($picked)
            ->map(fn (int $qty, int $id) => [
                'product' => $products[$id],
                'qty' => $qty,
                'subtotal' => $products[$id]->price * $qty,
            ])
            ->sortBy(fn (array $item) => $item['product']->category_id)
            ->values();

        return [
            'items' => $items,
            'total' => $total,
            'servings' => (int) floor($servings),
            'coverage' => $target > 0 ? min(100, (int) floor($servings / $target * 100)) : 100,
            'categories' => count($usedCategories),
            'signature' => collect($picked)->sortKeys()->map(fn ($qty, $id) => "$id:$qty")->implode(','),
        ];
    }

    private function score(array $row, array $profile, array $state): float
    {
        $p = $row['product'];
        $remaining = $state['remaining'];
        $target = max(1, $state['target']);

        $score = $row['relevance'] * $profile['relevance']
            + $row['value'] * $profile['value']
            + (isset($state['used'][$p->category_id]) ? 0 : $profile['variety']);

        // Produk yang sama berulang-ulang terasa membosankan.
        $score -= ($state['picked'][$p->id] ?? 0) * 0.9;

        if ($remaining > 0) {
            // Kemasan yang pas dengan sisa kebutuhan lebih baik daripada yang berlebihan.
            $useful = min($row['servings'], $remaining) / $target;
            $waste = max(0, $row['servings'] - $remaining) / $target;
            $score += $useful * 1.2 - $waste * 0.8;
        }

        // Lihat satu langkah ke depan: jangan habiskan budget untuk satu produk
        // kalau sisanya tidak cukup lagi untuk menutup porsi atau menambah jenis.
        $budgetAfter = $state['budget_left'] - $p->price;
        $needAfter = max(0, $remaining - $row['servings']);
        $isNewKind = ! isset($state['picked'][$p->id]);
        $kindsAfter = $state['kinds_missing'] - ($isNewKind ? 1 : 0);

        if ($needAfter > 0 && $budgetAfter < $needAfter * $state['cheapest_serving']) {
            $score -= 2.0;
        }

        if ($kindsAfter > 0 && $budgetAfter < $state['cheapest_price']) {
            $score -= 1.5;
        }

        return $score;
    }

    /** Minimal jenis produk supaya bundle tidak terasa "cuma satu barang". */
    private function minimumKinds(string $profile, int $target): int
    {
        if ($profile === 'hemat') {
            return 2;
        }

        return match (true) {
            $target <= 3 => 2,
            $target <= 12 => 3,
            default => 4,
        };
    }

    /** Batas atas untuk budget "> Rp100.000" yang tidak punya angka maksimum. */
    private function openEndedCap(Collection $pool, int $target, int $min): int
    {
        $estimate = (int) (ceil($target * $pool->median('per_serving') * 1.4 / 10000) * 10000);

        return min(600000, max($min + 50000, $estimate));
    }

    private function name(string $base, string $profile): string
    {
        return match ($profile) {
            'hemat' => "$base Hemat",
            'variatif' => "$base Variasi",
            default => "$base Bundle",
        };
    }

    private function tagline(string $profile): string
    {
        return match ($profile) {
            'hemat' => 'Porsi tercukupi dengan harga serendah mungkin.',
            'variatif' => 'Jenis makanan paling beragam, biar tidak bosan.',
            default => 'Paling cocok dengan kebutuhan dan rentang budgetmu.',
        };
    }
}
