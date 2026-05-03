<?php

namespace App\Services;

use App\Jobs\NotifyOrderStatusChanged;
use App\Jobs\SendOrderConfirmationEmail;
use App\Models\Coupon;
use App\Models\CouponUse;
use App\Models\Order;
use App\Models\OrderStatusHistory;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class OrderService
{
    public function __construct(
        private readonly CartService   $cartService,
        private readonly CouponService $couponService,
        private readonly StockService  $stockService,
    ) {}

    /**
     * Creates an order — recalculates totals server-side, never trusts frontend values.
     *
     * @param  array{
     *     items: array<int, array{variation_id: string, quantity: int}>,
     *     coupon_code: string|null,
     *     shipping_address: array,
     *     payment_method: string,
     * } $data
     */
    public function create(array $data, User $user): Order
    {
        return DB::transaction(function () use ($data, $user) {
            $coupon = null;
            $discountCoupon = '0.00';

            // Recalculate promotions server-side
            $cart = $this->cartService->calculate($data['items']);

            if (! empty($data['coupon_code'])) {
                $coupon = $this->couponService->validate($data['coupon_code'], $user, $cart->total);
                $discountCoupon = $this->couponService->calculateDiscount($coupon, $cart->total);

                // Recalculate with coupon discount applied
                $cart = $this->cartService->calculate($data['items'], $discountCoupon);
            }

            $order = Order::create([
                'user_id'             => $user->id,
                'status'              => 'pending',
                'subtotal'            => $cart->subtotal,
                'discount_promotions' => $cart->discountPromotions,
                'discount_coupon'     => $cart->discountCoupon,
                'coupon_id'           => $coupon?->id,
                'total'               => $cart->total,
                'shipping_address'    => $data['shipping_address'],
            ]);

            foreach ($cart->items as $item) {
                $order->items()->create([
                    'variation_id' => $item->variationId,
                    'quantity'     => $item->quantity,
                    'unit_price'   => $item->unitPrice,
                    'discount'     => $item->discount,
                    'subtotal'     => $item->subtotal,
                ]);
            }

            if ($coupon) {
                $this->recordCouponUse($coupon, $user, $order);
            }

            $this->stockService->reserve($data['items'], $order, $user);

            OrderStatusHistory::create([
                'order_id'   => $order->id,
                'status'     => 'pending',
                'changed_by' => $user->id,
            ]);

            SendOrderConfirmationEmail::dispatch($order);

            return $order;
        });
    }

    /**
     * Transitions an order to a new status and records the history entry.
     *
     * Only admin users call this. Dispatches real-time notification job.
     */
    public function updateStatus(Order $order, string $newStatus, User $admin, ?string $note = null): void
    {
        DB::transaction(function () use ($order, $newStatus, $admin, $note) {
            $order->update(['status' => $newStatus]);

            OrderStatusHistory::create([
                'order_id'   => $order->id,
                'status'     => $newStatus,
                'note'       => $note,
                'changed_by' => $admin->id,
            ]);

            if ($newStatus === 'cancelled') {
                $this->stockService->release($order, $admin);

                if ($order->coupon_id) {
                    $this->reverseCouponUse($order);
                }
            }
        });

        NotifyOrderStatusChanged::dispatch($order, $newStatus, $note);
    }

    private function recordCouponUse(Coupon $coupon, User $user, Order $order): void
    {
        CouponUse::create([
            'coupon_id' => $coupon->id,
            'user_id'   => $user->id,
            'order_id'  => $order->id,
        ]);

        $coupon->increment('used_count');
    }

    private function reverseCouponUse(Order $order): void
    {
        $use = $order->couponUse;

        if (! $use) {
            return;
        }

        $order->coupon?->decrement('used_count');
        $use->delete();
    }
}
