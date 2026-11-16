<?php

use App\Http\Controllers\Admin;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\BundleController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\ProductController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Toko (customer)
|--------------------------------------------------------------------------
*/
Route::get('/', [HomeController::class, 'index'])->name('home');

Route::get('/produk', [ProductController::class, 'index'])->name('products.index');
Route::get('/produk/{product}', [ProductController::class, 'show'])->name('products.show');

// Smart Bundle
Route::prefix('bundle')->name('bundle.')->controller(BundleController::class)->group(function () {
    Route::get('/', 'index')->name('index');
    Route::get('/hasil', 'result')->name('result');
    Route::post('/pilih', 'choose')->name('choose');
    Route::post('/keranjang', 'addRecommended')->name('add');
    Route::get('/racik', 'customize')->name('customize');
    Route::put('/racik', 'update')->name('update');
    Route::post('/racik/keranjang', 'addCustomized')->name('customize.add');
});

// Keranjang (session)
Route::prefix('keranjang')->name('cart.')->controller(CartController::class)->group(function () {
    Route::get('/', 'index')->name('index');
    Route::post('/', 'store')->name('store');
    Route::delete('/', 'clear')->name('clear');
    Route::patch('/{key}', 'update')->name('update');
    Route::delete('/{key}', 'destroy')->name('destroy');
});

/*
|--------------------------------------------------------------------------
| Autentikasi
|--------------------------------------------------------------------------
*/
Route::middleware('guest')->controller(AuthController::class)->group(function () {
    Route::get('/masuk', 'showLogin')->name('login');
    Route::post('/masuk', 'login')->middleware('throttle:6,1');
    Route::get('/daftar', 'showRegister')->name('register');
    Route::post('/daftar', 'register');
});

Route::post('/keluar', [AuthController::class, 'logout'])->middleware('auth')->name('logout');

/*
|--------------------------------------------------------------------------
| Checkout & pesanan (wajib login)
|--------------------------------------------------------------------------
*/
Route::middleware('auth')->group(function () {
    Route::get('/checkout', [CheckoutController::class, 'create'])->name('checkout.create');
    Route::post('/checkout', [CheckoutController::class, 'store'])->name('checkout.store');

    Route::get('/pesanan', [OrderController::class, 'index'])->name('orders.index');
    Route::get('/pesanan/{order}', [OrderController::class, 'show'])->name('orders.show');
});

/*
|--------------------------------------------------------------------------
| Admin / Owner
|--------------------------------------------------------------------------
*/
Route::prefix('admin')->name('admin.')->middleware(['auth', 'admin'])->group(function () {
    Route::get('/', Admin\DashboardController::class)->name('dashboard');

    Route::resource('produk', Admin\ProductController::class)
        ->except('show')
        ->names('products')
        ->parameters(['produk' => 'product']);

    Route::resource('kategori', Admin\CategoryController::class)
        ->except(['show', 'create'])
        ->names('categories')
        ->parameters(['kategori' => 'category']);

    Route::get('/stok', [Admin\StockController::class, 'index'])->name('stock.index');
    Route::patch('/stok/{product}', [Admin\StockController::class, 'update'])->name('stock.update');

    Route::get('/pesanan', [Admin\OrderController::class, 'index'])->name('orders.index');
    Route::get('/pesanan/{order}', [Admin\OrderController::class, 'show'])->name('orders.show');
    Route::patch('/pesanan/{order}', [Admin\OrderController::class, 'update'])->name('orders.update');

    Route::get('/laporan', Admin\ReportController::class)->name('reports');
});
