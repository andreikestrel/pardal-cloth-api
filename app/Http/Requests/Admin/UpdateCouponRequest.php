<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateCouponRequest extends FormRequest
{
    public function rules(): array
    {
        $couponId = $this->route('coupon')?->id;

        return [
            'code'              => ['required', 'string', 'max:50', Rule::unique('coupons', 'code')->ignore($couponId)],
            'discount_type'     => ['required', Rule::in(['percentage', 'fixed'])],
            'discount_value'    => ['required', 'numeric', 'min:0'],
            'min_order_amount'  => ['nullable', 'numeric', 'min:0'],
            'max_uses'          => ['nullable', 'integer', 'min:1'],
            'uses_per_user'     => ['nullable', 'integer', 'min:1'],
            'active'            => ['boolean'],
            'starts_at'         => ['nullable', 'date'],
            'ends_at'           => ['nullable', 'date', 'after_or_equal:starts_at'],
        ];
    }
}
