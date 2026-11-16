<?php

namespace Database\Seeders;

use App\Enums\OrderStatus;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Illuminate\Database\Seeder;

class OrderSeeder extends Seeder
{
    /**
     * Pesanan contoh 14 hari terakhir supaya dashboard & laporan langsung berisi.
     * Stok produk tidak dikurangi oleh seeder ini.
     */
    public function run(): void
    {
        if (Order::exists()) {
            return;
        }

        mt_srand(2026); // hasil yang sama setiap kali di-seed

        $customers = User::where('role', User::ROLE_CUSTOMER)->get();
        $products = Product::all()->keyBy('slug');
        $payments = array_keys(config('frozemate.payments'));

        $bundles = [
            'Kos Saver Bundle' => ['nugget-ayam-original' => 1, 'sosis-ayam-mini' => 1, 'dimsum-ayam' => 1],
            'Family Feast Bundle' => ['bakso-sapi-halus' => 1, 'chicken-karaage' => 1, 'kentang-goreng-shoestring' => 2, 'siomay-ikan-tenggiri' => 1],
            'Lunchbox Hero Bundle' => ['nugget-ayam-keju' => 1, 'chicken-katsu' => 1, 'hash-brown' => 1],
            'Party Platter Bundle' => ['dimsum-mix-party' => 1, 'spicy-wings' => 1, 'risoles-mayo' => 2, 'kentang-wedges-berbumbu' => 1],
        ];

        $singles = $products->keys()->all();

        // Jumlah pesanan per hari, dari 13 hari lalu sampai hari ini.
        $perDay = [2, 1, 3, 2, 0, 4, 3, 2, 3, 1, 4, 3, 5, 3];

        foreach ($perDay as $index => $count) {
            $daysAgo = 13 - $index;

            for ($n = 0; $n < $count; $n++) {
                $customer = $customers[mt_rand(0, $customers->count() - 1)];
                $placedAt = now()->subDays($daysAgo)->setTime(mt_rand(9, 19), mt_rand(0, 59));

                if ($placedAt->isFuture()) {
                    $placedAt = now()->subMinutes(mt_rand(5, 90));
                }

                $lines = [];

                if (mt_rand(1, 100) <= 60) {
                    $name = array_rand($bundles);
                    foreach ($bundles[$name] as $slug => $qty) {
                        $lines[] = [$slug, $qty, $name];
                    }
                }

                for ($extra = mt_rand(0, 2); $extra > 0 || empty($lines); $extra--) {
                    $lines[] = [$singles[mt_rand(0, count($singles) - 1)], mt_rand(1, 2), null];
                }

                $status = match (true) {
                    $daysAgo >= 3 => mt_rand(1, 100) <= 8 ? OrderStatus::Cancelled : OrderStatus::Completed,
                    $daysAgo >= 1 => [OrderStatus::Completed, OrderStatus::Ready, OrderStatus::Processing][mt_rand(0, 2)],
                    default => [OrderStatus::Pending, OrderStatus::Pending, OrderStatus::Processing][mt_rand(0, 2)],
                };

                $items = collect($lines)->map(function (array $line) use ($products) {
                    [$slug, $qty, $bundle] = $line;
                    $product = $products[$slug];

                    return [
                        'product_id' => $product->id,
                        'product_name' => $product->name,
                        'bundle_name' => $bundle,
                        'price' => $product->price,
                        'quantity' => $qty,
                        'subtotal' => $product->price * $qty,
                        'created_at' => null,
                        'updated_at' => null,
                    ];
                });

                $order = new Order([
                    'user_id' => $customer->id,
                    'customer_name' => $customer->name,
                    'phone' => $customer->phone,
                    'address' => $customer->address,
                    'payment_method' => $payments[mt_rand(0, count($payments) - 1)],
                    'total' => $items->sum('subtotal'),
                    'status' => $status,
                ]);
                $order->created_at = $placedAt;
                $order->updated_at = $placedAt;
                $order->save();

                $order->items()->createMany($items->map(fn ($item) => array_merge($item, [
                    'created_at' => $placedAt,
                    'updated_at' => $placedAt,
                ]))->all());
            }
        }
    }
}
