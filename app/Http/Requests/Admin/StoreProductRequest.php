<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class StoreProductRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'name'                        => ['required', 'string', 'max:255'],
            'slug'                        => ['required', 'string', 'max:255', 'unique:products,slug'],
            'description'                 => ['nullable', 'string'],
            'category_id'                 => ['required', 'uuid', 'exists:categories,id'],
            'image'                       => ['nullable', 'image', 'max:4096'],

            'variations'                  => ['required', 'array', 'min:1'],
            'variations.*.size'           => ['required', 'string', 'max:10'],
            'variations.*.color'          => ['required', 'string', 'max:50'],
            'variations.*.price'          => ['required', 'numeric', 'min:0'],
            'variations.*.stock'          => ['required', 'integer', 'min:0'],
            'variations.*.sku'            => ['nullable', 'string', 'max:100'],
            'variations.*.barcode'        => ['nullable', 'string', 'max:100', 'distinct'],
        ];
    }
}
