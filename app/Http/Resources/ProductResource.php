<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'          => $this->id,
            'name'        => $this->name,
            'slug'        => $this->slug,
            'description' => $this->description,
            'category'    => $this->whenLoaded('category'),
            'cover'       => $this->getFirstMediaUrl('cover'),
            'variations'  => ProductVariationResource::collection($this->whenLoaded('variations')),
            'created_at'  => $this->created_at,
        ];
    }
}
