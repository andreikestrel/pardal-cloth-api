<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class UpdateBlogPostRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'title'            => ['required', 'string', 'max:200'],
            'excerpt'          => ['nullable', 'string', 'max:500'],
            'body_html'        => ['required', 'string'],
            'blog_category_id' => ['nullable', 'uuid', 'exists:blog_categories,id'],
            'published_at'     => ['nullable', 'date'],
            'active'           => ['boolean'],
            'cover'            => ['nullable', 'image', 'max:6144'],
            'tag_ids'          => ['nullable', 'array'],
            'tag_ids.*'        => ['integer', 'exists:tags,id'],
        ];
    }
}
