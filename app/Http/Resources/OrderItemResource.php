<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OrderItemResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'           => $this->id,
            'variation_id' => $this->variation_id,
            // Read directly from the eager-loaded variation/product relations.
            // Controllers call $order->load(['items.variation.product']).
            'product_name' => $this->variation?->product?->name,
            'product_slug' => $this->variation?->product?->slug,
            'size'         => $this->variation?->size,
            'color'        => $this->variation?->color,
            'quantity'     => $this->quantity,
            'unit_price'   => $this->unit_price,
            'subtotal'     => $this->subtotal,
            'discount'     => $this->discount,
        ];
    }
}
