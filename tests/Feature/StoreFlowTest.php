<?php

namespace Tests\Feature;

use App\Enums\OrderStatus;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use App\Services\BundleRecommender;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StoreFlowTest extends TestCase
{
    use RefreshDatabase;

    protected bool $seed = true;

    public function test_halaman_toko_bisa_dibuka(): void
    {
        $product = Product::first();

        $this->get('/')->assertOk()->assertSee('Smart Frozen Food Bundle');
        $this->get('/produk')->assertOk()->assertSee($product->category->name);
        $this->get('/produk/'.$product->slug)->assertOk()->assertSee($product->name);
        $this->get('/bundle')->assertOk();
        $this->get('/bundle/racik')->assertOk();
        $this->get('/keranjang')->assertOk()->assertSee('masih kosong');
        $this->get('/masuk')->assertOk();
        $this->get('/daftar')->assertOk();
    }

    public function test_pencarian_dan_filter_produk(): void
    {
        $this->get('/produk?q=nugget')->assertOk()->assertSee('Nugget Ayam Original')->assertDontSee('Fish Ball');
        $this->get('/produk?kategori=sosis')->assertOk()->assertSee('Sosis Ayam Mini')->assertDontSee('Nugget Ayam Keju');
        $this->get('/produk?kebutuhan=acara&urut=termurah')->assertOk()->assertSee('Cireng Rujak');
        $this->get('/produk?q=tidakadaprodukini')->assertOk()->assertSee('Belum ketemu');
    }

    public function test_rekomendasi_bundle_tidak_melewati_budget_dan_stok(): void
    {
        $recommender = app(BundleRecommender::class);

        foreach (array_keys(config('frozemate.needs')) as $need) {
            foreach (array_keys(config('frozemate.budgets')) as $budgetKey) {
                foreach ([[1, 3], [2, 5], [5, 10]] as [$people, $meals]) {
                    $bundles = $recommender->recommend($need, $people, $budgetKey, $meals);
                    $max = config("frozemate.budgets.$budgetKey.max");

                    $this->assertNotEmpty($bundles, "Tidak ada bundle untuk $need/$people/$budgetKey/$meals");

                    foreach ($bundles as $bundle) {
                        if ($max) {
                            $this->assertLessThanOrEqual($max, $bundle['total']);
                        }

                        $this->assertSame($bundle['total'], $bundle['items']->sum('subtotal'));

                        foreach ($bundle['items'] as $item) {
                            $this->assertLessThanOrEqual($item['product']->stock, $item['qty']);
                            $this->assertGreaterThan(0, $item['product']->stock);
                        }
                    }
                }
            }
        }
    }

    public function test_halaman_hasil_bundle_dan_validasinya(): void
    {
        $this->get('/bundle/hasil?need=kos&people=1&budget=b2&meals=5')
            ->assertOk()
            ->assertSee('Kos Saver Bundle')
            ->assertSee('Rekomendasi utama');

        $this->get('/bundle/hasil?need=ngawur&people=1&budget=b2&meals=5')
            ->assertRedirect('/bundle')
            ->assertSessionHasErrors('need');
    }

    public function test_racik_bundle_menghitung_ulang_total(): void
    {
        $params = ['need' => 'kos', 'people' => 1, 'budget' => 'b2', 'meals' => 5, 'variant' => 'pas'];

        $this->post('/bundle/pilih', $params)->assertRedirect('/bundle/racik');
        $this->get('/bundle/racik')->assertOk()->assertSee('Kos Saver Bundle');

        $nugget = Product::where('slug', 'nugget-ayam-original')->first();
        $fishBall = Product::where('slug', 'fish-ball')->first();

        $this->putJson('/bundle/racik', ['items' => [
            ['id' => $nugget->id, 'qty' => 2],
            ['id' => $fishBall->id, 'qty' => 1],
        ]])
            ->assertOk()
            ->assertJsonPath('total', $nugget->price * 2 + $fishBall->price)
            ->assertJsonPath('count', 3)
            ->assertJsonPath('budget.state', 'ok');

        // Qty di atas stok dipotong oleh server.
        $this->putJson('/bundle/racik', ['items' => [['id' => $nugget->id, 'qty' => 20]]])
            ->assertOk()
            ->assertJsonPath('lines.0.qty', $nugget->stock)
            ->assertJsonPath('budget.state', 'over');

        $this->putJson('/bundle/racik', ['items' => [['id' => $fishBall->id, 'qty' => 2]]])->assertOk();
        $this->post('/bundle/racik/keranjang')->assertRedirect('/keranjang');
        $this->get('/keranjang')->assertOk()->assertSee('Kos Saver Bundle')->assertSee('Fish Ball');
    }

    public function test_keranjang_tambah_ubah_hapus(): void
    {
        $product = Product::where('slug', 'chicken-katsu')->first();

        $this->post('/keranjang', ['product_id' => $product->id, 'qty' => 2])->assertRedirect();
        $this->get('/keranjang')->assertSee('Chicken Katsu')->assertSee(rupiah($product->price * 2));

        $key = $product->id.'-solo';
        $this->patch("/keranjang/$key", ['qty' => 3])->assertRedirect();
        $this->assertSame(3, session('cart.lines')[$key]['qty']);

        // Tidak bisa melebihi stok.
        $this->patch("/keranjang/$key", ['qty' => 50])->assertRedirect();
        $this->assertSame($product->stock, session('cart.lines')[$key]['qty']);

        $this->delete("/keranjang/$key")->assertRedirect();
        $this->assertEmpty(session('cart.lines'));
    }

    public function test_checkout_wajib_login(): void
    {
        $this->get('/checkout')->assertRedirect('/masuk');
        $this->get('/pesanan')->assertRedirect('/masuk');
    }

    public function test_checkout_menyimpan_order_dan_mengurangi_stok(): void
    {
        $customer = User::where('role', User::ROLE_CUSTOMER)->first();
        $product = Product::where('slug', 'dimsum-ayam')->first();
        $stockBefore = $product->stock;
        $ordersBefore = Order::count();

        $this->actingAs($customer);
        $this->post('/keranjang', ['product_id' => $product->id, 'qty' => 2]);
        $this->post('/bundle/keranjang', ['need' => 'bekal', 'people' => 2, 'budget' => 'b1', 'meals' => 3, 'variant' => 'pas']);

        $this->get('/checkout')->assertOk()->assertSee('Lunchbox Hero Bundle');

        // Validasi form.
        $this->post('/checkout', ['customer_name' => '', 'phone' => '123', 'address' => 'x', 'payment_method' => 'bitcoin'])
            ->assertSessionHasErrors(['customer_name', 'phone', 'address', 'payment_method']);

        $response = $this->post('/checkout', [
            'customer_name' => 'Dinda Maharani',
            'phone' => '0813-1122-3344',
            'address' => 'Kos Melati No. 12, Jl. Dipatiukur, Bandung',
            'payment_method' => 'transfer',
            'notes' => 'Titip di satpam',
        ]);

        $order = Order::latest('id')->first();

        $response->assertRedirect('/pesanan/'.$order->code);
        $this->assertSame($ordersBefore + 1, Order::count());
        $this->assertSame(OrderStatus::Pending, $order->status);
        $this->assertSame($order->total, (int) $order->items->sum('subtotal'));
        $this->assertTrue($order->items->contains('bundle_name', 'Lunchbox Hero Bundle'));
        $this->assertSame($stockBefore - 2, $product->fresh()->stock);
        $this->assertEmpty(session('cart.lines'));

        $this->get('/pesanan/'.$order->code)->assertOk()->assertSee('#'.$order->code);
        $this->get('/pesanan')->assertOk()->assertSee('#'.$order->code);
    }

    public function test_customer_tidak_bisa_melihat_pesanan_orang_lain(): void
    {
        [$first, $second] = User::where('role', User::ROLE_CUSTOMER)->take(2)->get();
        $order = Order::where('user_id', $first->id)->first();

        $this->actingAs($second)->get('/pesanan/'.$order->code)->assertNotFound();
    }

    public function test_daftar_dan_masuk(): void
    {
        $this->post('/daftar', [
            'name' => 'Tester Baru',
            'email' => 'baru@frozemate.test',
            'password' => 'rahasia123',
            'password_confirmation' => 'rahasia123',
        ])->assertRedirect('/');

        $this->assertAuthenticated();
        $this->assertSame(User::ROLE_CUSTOMER, auth()->user()->role);

        $this->post('/keluar')->assertRedirect('/');
        $this->assertGuest();

        $this->post('/masuk', ['email' => 'baru@frozemate.test', 'password' => 'salah'])->assertSessionHasErrors('email');
        $this->post('/masuk', ['email' => 'baru@frozemate.test', 'password' => 'rahasia123'])->assertRedirect('/');
        $this->assertAuthenticated();
    }
}
