<?php

namespace Tests\Feature;

use App\Enums\OrderStatus;
use App\Models\Category;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class AdminTest extends TestCase
{
    use RefreshDatabase;

    protected bool $seed = true;

    private function admin(): User
    {
        return User::where('role', User::ROLE_ADMIN)->first();
    }

    public function test_halaman_admin_dibatasi_middleware(): void
    {
        $this->get('/admin')->assertRedirect('/masuk');

        $customer = User::where('role', User::ROLE_CUSTOMER)->first();
        $this->actingAs($customer)->get('/admin')->assertForbidden();
        $this->actingAs($customer)->get('/admin/produk')->assertForbidden();
    }

    public function test_admin_login_diarahkan_ke_dashboard(): void
    {
        $this->post('/masuk', ['email' => 'admin@frozemate.test', 'password' => 'password'])
            ->assertRedirect('/admin');
    }

    public function test_semua_halaman_admin_bisa_dibuka(): void
    {
        $this->actingAs($this->admin());
        $order = Order::first();
        $product = Product::first();
        $category = Category::first();

        foreach ([
            '/admin', '/admin/laporan', '/admin/laporan?rentang=30', '/admin/produk', '/admin/produk/create',
            "/admin/produk/{$product->slug}/edit", '/admin/kategori', "/admin/kategori/{$category->slug}/edit",
            '/admin/stok', '/admin/stok?tampil=menipis', '/admin/pesanan', '/admin/pesanan?status=pending',
            "/admin/pesanan/{$order->code}",
        ] as $url) {
            $this->get($url)->assertOk();
        }
    }

    public function test_crud_produk(): void
    {
        Storage::fake('uploads');
        $this->actingAs($this->admin());
        $category = Category::first();

        $this->post('/admin/produk', ['name' => '', 'price' => 100])->assertSessionHasErrors(['name', 'price', 'category_id', 'stock']);

        $this->post('/admin/produk', [
            'category_id' => $category->id,
            'name' => 'Tahu Bakso Beku',
            'description' => 'Tahu isi bakso sapi.',
            'price' => 21000,
            'stock' => 12,
            'weight_grams' => 300,
            'servings' => 4,
            'cook_method' => 'Kukus 8 menit',
            'tags' => ['kos', 'keluarga'],
            'is_active' => '1',
            // PNG 1×1 asli, supaya tes tidak bergantung pada ekstensi GD.
            'image' => UploadedFile::fake()->createWithContent('tahu.png', base64_decode(
                'iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mP8z8BQDwAEhQGAhKmMIQAAAABJRU5ErkJggg=='
            )),
        ])->assertRedirect('/admin/produk');

        $product = Product::where('slug', 'tahu-bakso-beku')->firstOrFail();
        $this->assertSame(['kos', 'keluarga'], $product->tags);
        $this->assertTrue($product->is_active);
        Storage::disk('uploads')->assertExists($product->image);
        $this->get('/produk/tahu-bakso-beku')->assertOk();

        $this->put('/admin/produk/tahu-bakso-beku', [
            'category_id' => $category->id,
            'name' => 'Tahu Bakso Beku',
            'price' => 23000,
            'stock' => 3,
            'weight_grams' => 300,
            'servings' => 4,
            'remove_image' => '1',
        ])->assertRedirect('/admin/produk');

        $product->refresh();
        $this->assertSame(23000, $product->price);
        $this->assertFalse($product->is_active);
        $this->assertNull($product->image);
        $this->get('/produk/tahu-bakso-beku')->assertNotFound(); // disembunyikan dari toko

        $this->delete('/admin/produk/tahu-bakso-beku')->assertRedirect('/admin/produk');
        $this->assertDatabaseMissing('products', ['slug' => 'tahu-bakso-beku']);
    }

    public function test_crud_kategori(): void
    {
        $this->actingAs($this->admin());

        $this->post('/admin/kategori', ['name' => 'Es Krim', 'tone' => 'blue', 'art' => 'box', 'is_side' => '1'])
            ->assertRedirect('/admin/kategori');
        $category = Category::where('slug', 'es-krim')->firstOrFail();
        $this->assertTrue($category->is_side);

        $this->put('/admin/kategori/es-krim', ['name' => 'Es Krim & Dessert', 'tone' => 'plum', 'art' => 'kudapan'])
            ->assertRedirect('/admin/kategori');
        $this->assertDatabaseHas('categories', ['slug' => 'es-krim-dessert', 'tone' => 'plum', 'is_side' => false]);

        // Kategori berisi produk tidak boleh dihapus.
        $used = Category::has('products')->first();
        $this->delete("/admin/kategori/{$used->slug}")->assertSessionHas('error');
        $this->assertDatabaseHas('categories', ['id' => $used->id]);

        $this->delete('/admin/kategori/es-krim-dessert')->assertSessionHas('info');
        $this->assertDatabaseMissing('categories', ['slug' => 'es-krim-dessert']);
    }

    public function test_kelola_stok(): void
    {
        $this->actingAs($this->admin());
        $product = Product::where('slug', 'crab-stick')->first();

        $this->patch("/admin/stok/{$product->slug}", ['adjust' => 10])->assertSessionHas('success');
        $this->assertSame($product->stock + 10, $product->fresh()->stock);

        $this->patch("/admin/stok/{$product->slug}", ['stock' => 7])->assertSessionHas('success');
        $this->assertSame(7, $product->fresh()->stock);

        $this->patch("/admin/stok/{$product->slug}", ['stock' => -3])->assertSessionHasErrors('stock');
    }

    public function test_alur_status_pesanan_dan_pembatalan(): void
    {
        $this->actingAs($this->admin());

        $order = Order::where('status', OrderStatus::Pending->value)->first();

        foreach ([OrderStatus::Processing, OrderStatus::Ready, OrderStatus::Completed] as $expected) {
            $this->patch("/admin/pesanan/{$order->code}", ['action' => 'advance'])->assertSessionHas('success');
            $this->assertSame($expected, $order->fresh()->status);
        }

        // Sudah selesai: tidak bisa maju atau dibatalkan lagi.
        $this->patch("/admin/pesanan/{$order->code}", ['action' => 'advance'])->assertSessionHas('error');
        $this->patch("/admin/pesanan/{$order->code}", ['action' => 'cancel'])->assertSessionHas('error');

        // Pembatalan mengembalikan stok.
        $other = Order::where('status', OrderStatus::Processing->value)->with('items')->first();
        $item = $other->items->first();
        $needed = $other->items->where('product_id', $item->product_id)->sum('quantity');
        $before = Product::find($item->product_id)->stock;

        $this->patch("/admin/pesanan/{$other->code}", ['action' => 'cancel'])->assertSessionHas('info');
        $this->assertSame(OrderStatus::Cancelled, $other->fresh()->status);
        $this->assertSame($before + $needed, Product::find($item->product_id)->stock);
    }
}
