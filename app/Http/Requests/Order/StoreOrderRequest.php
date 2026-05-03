<?php

namespace App\Http\Requests\Order;

use Illuminate\Foundation\Http\FormRequest;

class StoreOrderRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'items'                        => ['required', 'array', 'min:1'],
            'items.*.variation_id'         => ['required', 'uuid', 'exists:product_variations,id'],
            'items.*.quantity'             => ['required', 'integer', 'min:1', 'max:100'],

            'coupon_code'                  => ['nullable', 'string', 'exists:coupons,code'],

            'shipping_address'             => ['required', 'array'],
            'shipping_address.street'      => ['required', 'string', 'max:255'],
            'shipping_address.number'      => ['required', 'string', 'max:20'],
            'shipping_address.complement'  => ['nullable', 'string', 'max:100'],
            'shipping_address.district'    => ['required', 'string', 'max:100'],
            'shipping_address.city'        => ['required', 'string', 'max:100'],
            'shipping_address.state'       => ['required', 'string', 'size:2'],
            'shipping_address.zip_code'    => ['required', 'string', 'max:10'],

            // Frontend total is recorded for audit only — server always recalculates
            'frontend_total'               => ['required', 'numeric', 'min:0'],
        ];
    }
}
