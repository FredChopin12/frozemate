<?php

namespace App\Providers;

use App\Enums\OrderStatus;
use App\Models\Order;
use App\Models\Product;
use App\Services\CartService;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // Satu instance per request supaya data keranjang tidak di-query berulang.
        $this->app->scoped(CartService::class);
    }

    public function boot(): void
    {
        Paginator::defaultView('partials.pagination');

        // Jumlah isi keranjang untuk navbar toko.
        View::composer('layouts.app', function ($view) {
            $view->with('cartCount', app(CartService::class)->count());
        });

        // Angka penanda di menu admin: pesanan menunggu & stok menipis.
        View::composer('layouts.admin', function ($view) {
            $view->with([
                'navPending' => Order::where('status', OrderStatus::Pending->value)->count(),
                'navLowStock' => Product::lowStock()->count(),
            ]);
        });
    }
}
