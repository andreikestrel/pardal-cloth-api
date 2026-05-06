<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class FinalizePdvSaleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'items'                  => ['required', 'array', 'min:1'],
            'items.*.variation_id'   => ['required', 'uuid', 'exists:product_variations,id'],
            'items.*.quantity'       => ['required', 'integer', 'min:1'],
            'coupon_code'            => ['nullable', 'string'],
            'manual_discount'        => ['nullable', 'numeric', 'min:0'],
            'payment_method'         => ['required', 'in:pix,credit_card,cash'],
            'amount_paid'            => ['nullable', 'numeric', 'min:0'],
            'customer_name'          => ['nullable', 'string', 'max:255'],
            'customer_doc'           => ['nullable', 'string', 'max:20'],
        ];
    }
}
