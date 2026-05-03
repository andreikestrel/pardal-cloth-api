<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PaymentResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        if (! $this->resource) {
            return [];
        }

        return [
            'id'                  => $this->id,
            'gateway'             => $this->gateway,
            'method'              => $this->method,
            'status'              => $this->status,
            'amount'              => $this->amount,
            'gateway_payment_id'  => $this->gateway_payment_id,
            'payment_url'         => $this->payment_url,
            'pix_code'            => $this->pix_code,
            'pix_qr_code'         => $this->pix_qr_code,
            'boleto_url'          => $this->boleto_url,
            'expires_at'          => $this->expires_at,
            'paid_at'             => $this->paid_at,
        ];
    }
}
