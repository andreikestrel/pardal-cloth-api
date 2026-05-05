<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateProductRequest extends FormRequest
{
    public function rules(): array
    {
        $productId = $this->route('product')?->id;

        return [
            'name'        => ['required', 'string', 'max:255'],
            'slug'        => ['required', 'string', 'max:255', Rule::unique('products', 'slug')->ignore($productId)],
            'description' => ['nullable', 'string'],
            'base_price'  => ['required', 'numeric', 'min:0'],
            'category_id' => ['required', 'uuid', 'exists:categories,id'],
            'image'       => ['nullable', 'image', 'max:4096'],

            'variations'               => ['sometimes', 'array'],
            'variations.*.size'        => ['required_with:variations', 'string', 'max:10'],
            'variations.*.color'       => ['required_with:variations', 'string', 'max:50'],
            'variations.*.price'       => ['required_with:variations', 'numeric', 'min:0'],
            'variations.*.stock'       => ['required_with:variations', 'integer', 'min:0'],
            'variations.*.sku'         => ['nullable', 'string', 'max:100'],
            'variations.*.barcode'     => ['nullable', 'string', 'max:100', 'distinct'],

            'tag_ids'                  => ['nullable', 'array'],
            'tag_ids.*'                => ['integer', 'exists:tags,id'],
        ];
    }
}
