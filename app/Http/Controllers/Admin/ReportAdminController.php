<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class ReportAdminController extends Controller
{
    public function index(Request $request): Response
    {
        $days = max(1, (int) $request->get('period', 30));
        $from = now()->subDays($days)->startOfDay();

        $totalRevenue = (string) Order::where('status', 'delivered')
            ->where('created_at', '>=', $from)
            ->sum('total');

        $totalOrders = Order::where('created_at', '>=', $from)->count();

        $avgTicket = $totalOrders > 0
            ? bcdiv((string) $totalRevenue, (string) $totalOrders, 2)
            : '0.00';

        $revenueByDay = Order::where('status', 'delivered')
            ->where('created_at', '>=', $from)
            ->select(
                DB::raw('DATE(created_at) as date'),
                DB::raw('SUM(total) as total'),
                DB::raw('COUNT(*) as orders')
            )
            ->groupBy('date')
            ->orderBy('date')
            ->get()
            ->map(fn ($row) => [
                'date'   => $row->date,
                'total'  => (string) $row->total,
                'orders' => (int) $row->orders,
            ]);

        // Top products by quantity sold in period, via order_items → product_variations → products
        $topProducts = OrderItem::join('product_variations', 'order_items.variation_id', '=', 'product_variations.id')
            ->join('products', 'product_variations.product_id', '=', 'products.id')
            ->join('orders', 'order_items.order_id', '=', 'orders.id')
            ->where('orders.created_at', '>=', $from)
            ->whereIn('orders.status', ['confirmed', 'preparing', 'shipped', 'delivered'])
            ->select(
                'products.name',
                DB::raw('SUM(order_items.quantity) as qty'),
                DB::raw('SUM(order_items.quantity * order_items.unit_price) as revenue')
            )
            ->groupBy('products.id', 'products.name')
            ->orderByDesc('qty')
            ->limit(10)
            ->get()
            ->map(fn ($row) => [
                'name'    => $row->name,
                'qty'     => (int) $row->qty,
                'revenue' => (string) $row->revenue,
            ]);

        return Inertia::render('Admin/Reports/Index', [
            'report' => [
                'period_start'   => $from->toDateString(),
                'period_end'     => now()->toDateString(),
                'total_revenue'  => $totalRevenue,
                'total_orders'   => $totalOrders,
                'avg_ticket'     => $avgTicket,
                'revenue_by_day' => $revenueByDay,
                'top_products'   => $topProducts,
            ],
        ]);
    }
}
