<?php

namespace App\Http\Requests\Order;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class PayOrderRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'method'              => ['required', 'string', Rule::in(['pix', 'boleto', 'credit_card', 'checkout_pro'])],

            // Credit card fields — required only when method = credit_card
            'credit_card_token'   => ['required_if:method,credit_card', 'nullable', 'string'],
            'holder_name'         => ['required_if:method,credit_card', 'nullable', 'string', 'max:100'],
        ];
    }
}
