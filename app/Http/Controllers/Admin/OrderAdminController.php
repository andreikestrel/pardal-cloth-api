<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UpdateOrderStatusRequest;
use App\Models\Order;
use App\Services\OrderService;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class OrderAdminController extends Controller
{
    public function __construct(private readonly OrderService $orderService) {}

    public function index(): Response
    {
        return Inertia::render('Admin/Orders/Index', [
            'orders' => Order::with('user')->latest()->get(),
        ]);
    }

    public function show(Order $order): Response
    {
        return Inertia::render('Admin/Orders/Show', [
            'order' => new OrderResource($order->load(['user', 'items.variation.product', 'payment', 'statusHistory'])),
        ]);
    }

    public function updateStatus(UpdateOrderStatusRequest $request, Order $order): RedirectResponse
    {
        $this->orderService->updateStatus($order, $request->validated('status'), $request->user());

        return redirect()->route('admin.orders.show', $order)
            ->with('success', 'Order status updated successfully.');
    }
}
