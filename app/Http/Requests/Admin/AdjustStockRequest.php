<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class AdjustStockRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'variation_id' => ['required', 'uuid', 'exists:product_variations,id'],
            'quantity'     => ['required', 'integer', 'not_in:0'],
            'reason'       => ['required', 'string', 'max:255'],
        ];
    }
}
