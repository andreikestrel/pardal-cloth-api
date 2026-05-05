<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class UpdateGeneralSettingsRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'company_name'   => ['required', 'string', 'max:255'],
            'primary_color'  => ['required', 'string', 'regex:/^#[0-9A-Fa-f]{6}$/'],
            'secondary_color'=> ['required', 'string', 'regex:/^#[0-9A-Fa-f]{6}$/'],
            'accent_color'   => ['required', 'string', 'regex:/^#[0-9A-Fa-f]{6}$/'],
            'logo'           => ['nullable', 'image', 'max:2048'],
            'blog_enabled'   => ['boolean'],
        ];
    }
}
