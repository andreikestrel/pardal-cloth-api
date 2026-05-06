<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OrderResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'                  => $this->id,
            'status'              => $this->status,
            'subtotal'            => $this->subtotal,
            'discount_promotions' => $this->discount_promotions,
            'discount_coupon'     => $this->discount_coupon,
            'total'               => $this->total,
            'shipping_address'    => $this->shipping_address,
            'notes'               => $this->notes,
            'created_at'          => $this->created_at,
            'updated_at'          => $this->updated_at,
            'user'                => $this->whenLoaded('user'),
            'items'               => OrderItemResource::collection($this->whenLoaded('items')),
            'payment'             => $this->whenLoaded('payment', fn () => new PaymentResource($this->payment)),
            'status_history'      => $this->whenLoaded('statusHistory', fn () => $this->statusHistory->map(fn ($h) => [
                'id'         => $h->id,
                'status'     => $h->status,
                'note'       => $h->note,
                'created_at' => $h->created_at,
            ])->values()->all()),
        ];
    }
}
