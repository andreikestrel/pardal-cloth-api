<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class OpenCashSessionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'cash_register_id' => ['required', 'uuid', 'exists:cash_registers,id'],
            'opening_balance'  => ['required', 'numeric', 'min:0'],
        ];
    }
}
