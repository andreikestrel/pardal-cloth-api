<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\ProductVariation;
use Inertia\Inertia;
use Inertia\Response;

class DashboardAdminController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('Admin/Dashboard', [
            'kpis' => [
                'orders_today'   => Order::whereDate('created_at', today())->count(),
                'revenue_today'  => Order::whereDate('created_at', today())
                    ->where('status', 'delivered')
                    ->sum('total'),
                'pending_orders' => Order::where('status', 'pending')->count(),
                'low_stock'      => ProductVariation::whereColumn('stock', '<=', 'min_stock')->count(),
            ],
            'recentOrders' => Order::with('user')
                ->latest()
                ->take(5)
                ->get(),
            'lowStock' => ProductVariation::with('product')
                ->whereColumn('stock', '<=', 'min_stock')
                ->get(),
        ]);
    }
}
