<?php

namespace App\DataTransferObjects;

final class CartItemData
{
    public function __construct(
        public readonly string $variation_id,
        public readonly int $quantity,
        public readonly ?string $product_name = null,
        public readonly ?string $sku = null,
        public readonly ?string $unit_price = null,
        public readonly ?string $size = null,
        public readonly ?string $color = null,
        public readonly ?string $cover_url = null,
        public readonly ?string $discount = null,
        public readonly ?string $subtotal = null,
        public readonly ?string $promotion_applied = null,
    ) {}
}
