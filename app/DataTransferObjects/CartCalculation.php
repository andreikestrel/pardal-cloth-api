<?php

namespace App\DataTransferObjects;

final class CartCalculation
{
    /**
     * @param CartItemData[] $items
     * @param array<int, array{name: string, discount: string}> $promotionsApplied
     */
    public function __construct(
        public readonly array $items,
        public readonly string $subtotal,
        public readonly array $promotionsApplied,
        public readonly string $discountPromotions,
        public readonly string $discountCoupon,
        public readonly string $total,
    ) {}
}
