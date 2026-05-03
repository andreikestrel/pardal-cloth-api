<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductVariationResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'         => $this->id,
            'product_id' => $this->product_id,
            'size'       => $this->size,
            'color'      => $this->color,
            'price'      => $this->price,
            'stock'      => $this->stock,
            'sku'        => $this->sku,
        ];
    }
}
