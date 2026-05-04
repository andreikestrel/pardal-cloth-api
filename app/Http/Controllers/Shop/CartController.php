<?php

namespace App\Http\Controllers\Shop;

use App\Http\Controllers\Controller;
use App\Http\Requests\Shop\CalculateCartRequest;
use App\Http\Requests\Shop\ApplyCouponRequest;
use App\Services\CartService;
use App\Services\CouponService;
use Illuminate\Http\JsonResponse;
use Inertia\Inertia;
use Inertia\Response;

class CartController extends Controller
{
    public function __construct(
        private readonly CartService $cartService,
        private readonly CouponService $couponService,
    ) {}

    public function page(): Response
    {
        return Inertia::render('Shop/Cart');
    }

    public function calculate(CalculateCartRequest $request): JsonResponse
    {
        $calculation = $this->cartService->calculate($request->validated('items'));

        return response()->json($calculation);
    }

    public function applyCoupon(ApplyCouponRequest $request): JsonResponse
    {
        $items = $request->validated('items');
        $code  = $request->validated('coupon_code');

        $calculation = $this->cartService->calculate($items);
        $discount    = $this->couponService->calculateDiscount($code, $calculation->subtotal, $request->user());

        return response()->json([
            'discount'    => $discount,
            'coupon_code' => $code,
        ]);
    }
}
