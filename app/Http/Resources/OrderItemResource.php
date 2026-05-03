<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OrderItemResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'            => $this->id,
            'variation_id'  => $this->variation_id,
            'product_name'  => $this->product_name,
            'size'          => $this->size,
            'color'         => $this->color,
            'quantity'      => $this->quantity,
            'unit_price'    => $this->unit_price,
            'subtotal'      => $this->subtotal,
            'discount'      => $this->discount,
            'variation'     => $this->whenLoaded('variation', fn () => new ProductVariationResource($this->variation)),
        ];
    }
}
