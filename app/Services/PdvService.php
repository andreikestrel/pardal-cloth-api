<?php

namespace App\Services;

use App\Models\CashSession;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Payment;
use App\Models\ProductVariation;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class PdvService
{
    public function __construct(
        private readonly CartService $cartService,
        private readonly CouponService $couponService,
        private readonly StockService $stockService,
    ) {}

    /**
     * Searches products by name (fulltext) or barcode for the PDV product picker.
     * Returns a flat list of matching variations with parent product info.
     */
    public function searchProducts(string $query): Collection
    {
        $query = trim($query);

        if ($query === '') {
            return collect();
        }

        // Barcode lookup — exact match takes priority
        $byBarcode = ProductVariation::with('product')
            ->where('barcode', $query)
            ->where('stock', '>', 0)
            ->get();

        if ($byBarcode->isNotEmpty()) {
            return $this->formatVariations($byBarcode);
        }

        // Fulltext search on products, then load their variations
        // FULLTEXT index covers (name, description) — both columns must be listed in MATCH
        $productIds = DB::table('products')
            ->whereRaw('MATCH(name, description) AGAINST(? IN BOOLEAN MODE)', [$query . '*'])
            ->where('active', true)
            ->pluck('id');

        if ($productIds->isEmpty()) {
            // Fallback to LIKE for short queries that fulltext may miss
            $productIds = DB::table('products')
                ->where('name', 'like', '%' . $query . '%')
                ->where('active', true)
                ->pluck('id');
        }

        $variations = ProductVariation::with('product')
            ->whereIn('product_id', $productIds)
            ->orderBy('product_id')
            ->orderBy('size')
            ->orderBy('color')
            ->get();

        return $this->formatVariations($variations);
    }

    private function formatVariations(Collection $variations): Collection
    {
        // Group by product, return nested structure
        return $variations
            ->groupBy('product_id')
            ->map(function (Collection $group) {
                $product = $group->first()->product;

                return [
                    'id'         => $product->id,
                    'name'       => $product->name,
                    'cover_url'  => $product->cover_url ?? null,
                    'variations' => $group->map(fn ($v) => [
                        'id'      => $v->id,
                        'size'    => $v->size,
                        'color'   => $v->color,
                        'price'   => $v->price,
                        'stock'   => $v->stock,
                        'barcode' => $v->barcode,
                        'sku'     => $v->sku,
                    ])->values()->all(),
                ];
            })->values();
    }

    /**
     * Finalizes a PDV sale: creates the order, deducts stock, records payment.
     *
     * @param array{
     *   items: array<int, array{variation_id: string, quantity: int}>,
     *   coupon_code: string|null,
     *   manual_discount: string|null,
     *   payment_method: string,
     *   amount_paid: string|null,
     *   customer_name: string|null,
     *   customer_doc: string|null,
     * } $data
     */
    public function finalizeSale(array $data, CashSession $session, User $operator): Order
    {
        return DB::transaction(function () use ($data, $session, $operator) {
            $items   = $data['items'];
            $rawCalc = $this->cartService->calculate($items);

            // Coupon resolution (PDV sales may not have a user account, so use operator for validation)
            $coupon         = null;
            $discountCoupon = '0.00';

            if (! empty($data['coupon_code'])) {
                $coupon         = $this->couponService->validate($data['coupon_code'], $operator, $rawCalc->subtotal);
                $discountCoupon = $this->couponService->calculateDiscount($coupon, $rawCalc->subtotal);
            }

            // Apply coupon to get final calculation
            $calc = $this->cartService->calculate($items, $discountCoupon);

            // Manual discount on top (additional flat reduction by the operator)
            $manualDiscount = $data['manual_discount'] ?? '0.00';
            $total = bcsub($calc->total, $manualDiscount, 2);
            $total = bccomp($total, '0.00', 2) < 0 ? '0.00' : $total;

            // Validate stock before creating the order
            foreach ($items as $item) {
                $variation = ProductVariation::lockForUpdate()->find($item['variation_id']);

                if (! $variation || $variation->stock < $item['quantity']) {
                    throw ValidationException::withMessages([
                        'items' => "Estoque insuficiente para SKU {$variation?->sku}.",
                    ]);
                }
            }

            $order = Order::create([
                'user_id'             => null,
                'source'              => 'pdv',
                'cash_session_id'     => $session->id,
                'status'              => 'delivered',
                'subtotal'            => $calc->subtotal,
                'discount_promotions' => $calc->discountPromotions,
                'discount_coupon'     => $discountCoupon,
                'coupon_id'           => $coupon?->id,
                'total'               => $total,
                'shipping_address'    => null,
                'notes'               => null,
                'pdv_customer_name'   => $data['customer_name'] ?? null,
                'pdv_customer_doc'    => $data['customer_doc'] ?? null,
            ]);

            foreach ($calc->items as $cartItem) {
                OrderItem::create([
                    'order_id'     => $order->id,
                    'variation_id' => $cartItem->variationId,
                    'quantity'     => $cartItem->quantity,
                    'unit_price'   => $cartItem->unitPrice,
                    'discount'     => $cartItem->discount,
                    'subtotal'     => $cartItem->subtotal,
                ]);
            }

            // Deduct stock (reuse StockService::reserve which handles locking + movements)
            $this->stockService->reserve($items, $order, $operator);

            if ($coupon) {
                $coupon->increment('used_count');
            }

            // Record payment as approved immediately (no gateway for PDV)
            Payment::create([
                'order_id'           => $order->id,
                'gateway'            => 'manual',
                'gateway_payment_id' => 'pdv-' . $order->id,
                'method'             => $data['payment_method'],
                'status'             => 'approved',
                'amount'             => $total,
                'paid_at'            => now(),
            ]);

            return $order->load(['items.variation.product', 'cashSession.cashRegister', 'cashSession.operator']);
        });
    }

    /**
     * Generates a compact receipt PDF for a completed PDV order.
     */
    public function generateReceipt(Order $order): \Barryvdh\DomPDF\PDF
    {
        $order->loadMissing([
            'items.variation.product',
            'cashSession.cashRegister',
            'cashSession.operator',
            'payment',
        ]);

        $settings = \App\Models\Setting::first();

        return Pdf::loadView('pdf.receipt', [
            'order'    => $order,
            'settings' => $settings,
        ])->setPaper([0, 0, 226.77, 600], 'portrait'); // ~80mm width
    }
}
