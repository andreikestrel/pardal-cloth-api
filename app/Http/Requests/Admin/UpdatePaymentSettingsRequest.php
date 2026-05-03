<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdatePaymentSettingsRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'gateway'     => ['required', Rule::in(['mercadopago', 'asaas'])],
            'active'      => ['boolean'],
            'credentials' => ['required', 'array'],
        ];
    }
}
