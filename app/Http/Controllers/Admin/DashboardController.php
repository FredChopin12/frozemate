<?php

namespace App\Http\Controllers\Admin;

use App\Enums\OrderStatus;
use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Product;
use App\Services\SalesReport;

class DashboardController extends Controller
{
    public function __invoke(SalesReport $report)
    {
        $daily = $report->daily(7);

        return view('admin.dashboard', [
            'totalProducts' => Product::count(),
            'totalOrders' => Order::count(),
            'todaySales' => (int) Order::counted()->whereDate('created_at', today())->sum('total'),
            'todayOrders' => Order::whereDate('created_at', today())->count(),
            'yesterdaySales' => $daily[5]['revenue'] ?? 0,
            'lowStock' => Product::with('category')->lowStock()->orderBy('stock')->take(6)->get(),
            'lowStockCount' => Product::lowStock()->count(),
            'recentOrders' => Order::withCount('items')->latest()->take(7)->get(),
            'waiting' => Order::where('status', OrderStatus::Pending->value)->count(),
            'daily' => $daily,
            'weekRevenue' => $daily->sum('revenue'),
            'weekOrders' => $daily->sum('orders'),
            'topProducts' => $report->topProducts(30),
        ]);
    }
}
