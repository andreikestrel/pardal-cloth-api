<?php

namespace App\Services;

use App\DataTransferObjects\CartCalculation;
use App\DataTransferObjects\CartItemData;
use App\Models\ProductVariation;
use Illuminate\Support\Facades\DB;

class CartService
{
    public function __construct(
        private readonly PromotionService $promotionService,
    ) {}

    /**
     * Calculates the cart total with all active promotions applied.
     *
     * Loads current prices from the database — never trusts prices from the frontend.
     *
     * @param  array<int, array{variation_id: string, quantity: int}> $rawItems
     */
    public function calculate(array $rawItems, string $discountCoupon = '0.00'): CartCalculation
    {
        $variationIds = array_column($rawItems, 'variation_id');

        $variations = ProductVariation::with('product.category')
            ->whereIn('id', $variationIds)
            ->get()
            ->keyBy('id');

        $items = [];
        $subtotal = '0.00';

        foreach ($rawItems as $raw) {
            $variation = $variations->get($raw['variation_id']);

            if (! $variation) {
                continue;
            }

            $unitPrice = (string) $variation->price;
            $quantity  = (int) $raw['quantity'];
            $lineTotal = bcmul($unitPrice, (string) $quantity, 2);

            $items[] = [
                'variation'  => $variation,
                'quantity'   => $quantity,
                'unit_price' => $unitPrice,
                'discount'   => '0.00',
            ];

            $subtotal = bcadd($subtotal, $lineTotal, 2);
        }

        $result = $this->promotionService->apply($items);

        $discountPromotions = $result['discount_total'];
        $totalAfterPromotions = bcsub($subtotal, $discountPromotions, 2);
        $total = bcsub($totalAfterPromotions, $discountCoupon, 2);

        $cartItems = array_map(function (array $item) {
            $lineTotal = bcmul($item['unit_price'], (string) $item['quantity'], 2);
            $subtotal  = bcsub($lineTotal, $item['discount'], 2);

            return new CartItemData(
                variationId:  $item['variation']->id,
                quantity:     $item['quantity'],
                productName:  $item['variation']->product->name,
                sku:          $item['variation']->sku,
                unitPrice:    $item['unit_price'],
                discount:     $item['discount'],
                subtotal:     $subtotal,
            );
        }, $result['items']);

        return new CartCalculation(
            items:              $cartItems,
            subtotal:           $subtotal,
            promotionsApplied:  $result['promotions_applied'],
            discountPromotions: $discountPromotions,
            discountCoupon:     $discountCoupon,
            total:              max($total, '0.00'),
        );
    }
}
