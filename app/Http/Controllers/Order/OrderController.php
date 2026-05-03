<?php

namespace App\Http\Controllers\Order;

use App\Http\Controllers\Controller;
use App\Http\Requests\Order\StoreOrderRequest;
use App\Http\Requests\Order\PayOrderRequest;
use App\Http\Resources\OrderResource;
use App\Models\Order;
use App\Services\OrderService;
use App\Services\PaymentService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response as HttpResponse;
use Inertia\Inertia;
use Inertia\Response as InertiaResponse;

class OrderController extends Controller
{
    public function __construct(
        private readonly OrderService $orderService,
        private readonly PaymentService $paymentService,
    ) {}

    public function index(Request $request): InertiaResponse
    {
        $orders = $request->user()
            ->orders()
            ->with(['items.variation.product', 'payment'])
            ->latest()
            ->paginate(10);

        return Inertia::render('Orders/Index', [
            'orders' => OrderResource::collection($orders),
        ]);
    }

    public function show(Request $request, Order $order): InertiaResponse
    {
        abort_if($order->user_id !== $request->user()->id, 403);

        return Inertia::render('Orders/Show', [
            'order' => new OrderResource($order->load(['items.variation.product', 'payment', 'statusHistory'])),
        ]);
    }

    public function store(StoreOrderRequest $request): RedirectResponse
    {
        $order = $this->orderService->create($request->validated(), $request->user());

        return redirect()->route('orders.pay', $order);
    }

    public function pay(PayOrderRequest $request, Order $order): \Illuminate\Http\JsonResponse
    {
        abort_if($order->user_id !== $request->user()->id, 403);

        $result = $this->paymentService->initiate($order, $request->validated('method'));

        return response()->json($result);
    }

    public function paymentStatus(Request $request, Order $order): InertiaResponse
    {
        abort_if($order->user_id !== $request->user()->id, 403);

        return Inertia::render('Orders/PaymentStatus', [
            'order' => new OrderResource($order->load('payment')),
        ]);
    }

    public function invoice(Request $request, Order $order): HttpResponse
    {
        abort_if($order->user_id !== $request->user()->id, 403);

        return $this->orderService->generateInvoice($order);
    }
}
