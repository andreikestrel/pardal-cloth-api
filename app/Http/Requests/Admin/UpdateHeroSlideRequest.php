<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class UpdateHeroSlideRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'title'      => ['nullable', 'string', 'max:120'],
            'subtitle'   => ['nullable', 'string', 'max:200'],
            'link_url'   => ['nullable', 'string', 'max:500'],
            'link_label' => ['nullable', 'string', 'max:60'],
            'active'     => ['boolean'],
            'sort_order' => ['integer', 'min:0'],
            'image'      => ['nullable', 'image', 'max:4096'],
        ];
    }
}
