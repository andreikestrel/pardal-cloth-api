<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Product;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ReportAdminController extends Controller
{
    public function index(Request $request): Response
    {
        $period = $request->get('period', '30');

        $from = now()->subDays((int) $period);

        return Inertia::render('Admin/Reports/Index', [
            'period'         => $period,
            'totalRevenue'   => Order::where('status', 'delivered')
                ->where('created_at', '>=', $from)
                ->sum('total'),
            'orderCount'     => Order::where('created_at', '>=', $from)->count(),
            'recentOrders'   => Order::with('user')
                ->latest()
                ->take(10)
                ->get(),
            'topProducts'    => Product::withCount(['items as sold_count' => fn ($q) => $q->whereHas('order', fn ($q) => $q->where('created_at', '>=', $from))])
                ->orderByDesc('sold_count')
                ->take(10)
                ->get(),
        ]);
    }
}
