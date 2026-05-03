<?php

namespace App\Http\Requests\Shop;

use Illuminate\Foundation\Http\FormRequest;

class ApplyCouponRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'coupon_code'            => ['required', 'string', 'max:50'],
            'items'                  => ['required', 'array', 'min:1'],
            'items.*.variation_id'   => ['required', 'uuid', 'exists:product_variations,id'],
            'items.*.quantity'       => ['required', 'integer', 'min:1', 'max:100'],
        ];
    }
}
