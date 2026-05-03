<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateCategoryRequest extends FormRequest
{
    public function rules(): array
    {
        $categoryId = $this->route('category')?->id;

        return [
            'name'  => ['required', 'string', 'max:255'],
            'slug'  => ['required', 'string', 'max:255', Rule::unique('categories', 'slug')->ignore($categoryId)],
            'image' => ['nullable', 'image', 'max:4096'],
        ];
    }
}
