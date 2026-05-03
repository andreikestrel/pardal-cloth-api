<?php

namespace App\Services;

use App\Models\ProductVariation;
use App\Models\Promotion;
use Illuminate\Support\Collection;

class PromotionService
{
    /**
     * Applies all active promotions to the given cart items.
     *
     * Promotions are applied in ascending priority order. Only one promotion
     * of the same discount_type applies per item to avoid double discounts.
     *
     * @param  array<int, array{variation: ProductVariation, quantity: int, unit_price: string}> $items
     * @return array{items: array, promotions_applied: array, discount_total: string}
     */
    public function apply(array $items): array
    {
        $promotions = Promotion::query()
            ->where('active', true)
            ->where(function ($q) {
                $q->whereNull('starts_at')->orWhere('starts_at', '<=', now());
            })
            ->where(function ($q) {
                $q->whereNull('ends_at')->orWhere('ends_at', '>=', now());
            })
            ->orderBy('priority')
            ->get();

        // Track which discount_type has already been applied per variation id
        $appliedTypes = [];
        $promotionsApplied = [];
        $discountTotal = '0.00';

        foreach ($promotions as $promotion) {
            if (! $this->triggerMet($promotion, $items)) {
                continue;
            }

            $eligible = $this->getEligibleItems($promotion, $items);
            if ($eligible->isEmpty()) {
                continue;
            }

            $promotionDiscount = '0.00';

            foreach ($eligible as $index => $item) {
                $variationId = $item['variation']->id;

                // One promotion per discount_type per item
                if (in_array($promotion->discount_type, $appliedTypes[$variationId] ?? [])) {
                    continue;
                }

                $itemDiscount = $this->calculateItemDiscount($promotion, $item, $eligible);
                $items[$index]['discount'] = bcadd($items[$index]['discount'] ?? '0.00', $itemDiscount, 2);

                $appliedTypes[$variationId][] = $promotion->discount_type;
                $promotionDiscount = bcadd($promotionDiscount, $itemDiscount, 2);
            }

            if (bccomp($promotionDiscount, '0.00', 2) > 0) {
                $promotionsApplied[] = [
                    'name'     => $promotion->name,
                    'discount' => $promotionDiscount,
                ];
                $discountTotal = bcadd($discountTotal, $promotionDiscount, 2);
            }
        }

        return [
            'items'              => $items,
            'promotions_applied' => $promotionsApplied,
            'discount_total'     => $discountTotal,
        ];
    }

    private function triggerMet(Promotion $promotion, array $items): bool
    {
        return match ($promotion->trigger_type) {
            'min_qty'    => $this->totalQuantity($items) >= ($promotion->min_items ?? 1),
            'min_amount' => bccomp($this->subtotal($items), (string) $promotion->min_amount, 2) >= 0,
            'product'    => collect($items)->contains(fn ($i) => $i['variation']->product_id === $promotion->target_id),
            'category'   => collect($items)->contains(fn ($i) => $i['variation']->product->category_id === $promotion->target_id),
        };
    }

    private function getEligibleItems(Promotion $promotion, array $items): Collection
    {
        $all = collect($items)->map(fn ($item, $index) => array_merge($item, ['_index' => $index]));

        return match ($promotion->trigger_type) {
            'product'  => $all->filter(fn ($i) => $i['variation']->product_id === $promotion->target_id),
            'category' => $all->filter(fn ($i) => $i['variation']->product->category_id === $promotion->target_id),
            default    => $all,
        };
    }

    private function calculateItemDiscount(Promotion $promotion, array $item, Collection $allEligible): string
    {
        $lineTotal = bcmul($item['unit_price'], (string) $item['quantity'], 2);

        return match ($promotion->discount_type) {
            'percentage'   => bcmul($lineTotal, bcdiv((string) $promotion->discount_value, '100', 6), 2),
            'fixed'        => min((string) $promotion->discount_value, $lineTotal),
            'free_item'    => $this->freeItemDiscount($item),
            'free_shipping'=> '0.00', // Shipping discount applied at order level, not per item
        };
    }

    private function freeItemDiscount(array $item): string
    {
        // Cheapest item in the line becomes free (one unit)
        return $item['unit_price'];
    }

    private function totalQuantity(array $items): int
    {
        return array_sum(array_column($items, 'quantity'));
    }

    private function subtotal(array $items): string
    {
        return array_reduce($items, function (string $carry, array $item) {
            return bcadd($carry, bcmul($item['unit_price'], (string) $item['quantity'], 2), 2);
        }, '0.00');
    }
}
