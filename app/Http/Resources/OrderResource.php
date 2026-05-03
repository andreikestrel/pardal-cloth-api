<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OrderResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'               => $this->id,
            'status'           => $this->status,
            'subtotal'         => $this->subtotal,
            'discount'         => $this->discount,
            'shipping'         => $this->shipping,
            'total'            => $this->total,
            'coupon_code'      => $this->coupon_code,
            'shipping_address' => $this->shipping_address,
            'user'             => $this->whenLoaded('user'),
            'items'            => OrderItemResource::collection($this->whenLoaded('items')),
            'payment'          => new PaymentResource($this->whenLoaded('payment')),
            'status_history'   => $this->whenLoaded('statusHistory'),
            'created_at'       => $this->created_at,
        ];
    }
}
