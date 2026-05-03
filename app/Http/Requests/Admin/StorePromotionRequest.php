<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StorePromotionRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'name'           => ['required', 'string', 'max:255'],
            'discount_type'  => ['required', Rule::in(['percentage', 'fixed', 'free_item', 'free_shipping'])],
            'discount_value' => ['required', 'numeric', 'min:0'],
            'trigger_type'   => ['required', Rule::in(['min_qty', 'min_amount', 'product', 'category'])],
            'trigger_value'  => ['required'],
            'priority'       => ['required', 'integer', 'min:0'],
            'active'         => ['boolean'],
            'starts_at'      => ['nullable', 'date'],
            'ends_at'        => ['nullable', 'date', 'after_or_equal:starts_at'],
        ];
    }
}
