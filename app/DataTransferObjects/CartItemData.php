<?php

namespace App\DataTransferObjects;

final class CartItemData
{
    public function __construct(
        public readonly string $variationId,
        public readonly int $quantity,
        // Populated by CartService after loading variation from DB
        public readonly ?string $productName = null,
        public readonly ?string $sku = null,
        public readonly ?string $unitPrice = null,
        public readonly ?string $discount = null,
        public readonly ?string $subtotal = null,
        public readonly ?string $promotionApplied = null,
    ) {}
}
