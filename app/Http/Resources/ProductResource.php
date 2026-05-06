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
            'base_price'  => $this->base_price,
            'category_id' => $this->category_id,
            'category'    => $this->whenLoaded('category'),
            'cover'       => $this->getFirstMediaUrl('cover'),
            'variations'  => ProductVariationResource::collection($this->whenLoaded('variations')),
            'tags'        => $this->whenLoaded('tags', fn () => $this->tags->map(fn ($t) => [
                'id'    => $t->id,
                'name'  => $t->name,
                'color' => $t->color,
            ])->values()->all()),
            'created_at'  => $this->created_at,
        ];
    }
}
