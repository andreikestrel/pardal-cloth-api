<?php

namespace App\Http\Controllers\Shop;

use App\Http\Controllers\Controller;
use App\Models\BlogPost;
use App\Models\Product;
use App\Models\Tag;
use Inertia\Inertia;
use Inertia\Response;

class TagController extends Controller
{
    public function show(string $slug): Response
    {
        $tag = Tag::where('slug->pt', $slug)
            ->orWhere('slug->en', $slug)
            ->firstOrFail();

        $products = Product::with(['media', 'variations'])
            ->whereHas('tags', fn ($q) => $q->where('tags.id', $tag->id))
            ->where('active', true)
            ->latest()
            ->take(24)
            ->get()
            ->map(fn (Product $p) => [
                'id'         => $p->id,
                'name'       => $p->name,
                'slug'       => $p->slug,
                'base_price' => $p->base_price,
                'cover_url'  => $p->getFirstMediaUrl('images', 'thumb') ?: $p->getFirstMediaUrl('images'),
                'colors'     => $p->variations->pluck('color')->unique()->filter()->values(),
                'sizes'      => $p->variations->pluck('size')->unique()->filter()->values(),
            ]);

        $posts = BlogPost::with(['media', 'category', 'author'])
            ->whereHas('tags', fn ($q) => $q->where('tags.id', $tag->id))
            ->published()
            ->latest('published_at')
            ->take(12)
            ->get()
            ->map(fn (BlogPost $post) => [
                'id'           => $post->id,
                'title'        => $post->title,
                'slug'         => $post->slug,
                'excerpt'      => $post->excerpt,
                'published_at' => $post->published_at,
                'cover_url'      => $post->cover_url,
                'cover_position' => $post->cover_position,
                'category'       => $post->category ? [
                    'name'  => $post->category->name,
                    'color' => $post->category->color,
                ] : null,
            ]);

        return Inertia::render('Shop/TagLanding', [
            'tag'      => [
                'id'       => $tag->id,
                'name'     => $tag->name,
                'color'    => $tag->color,
                'link_url' => $tag->link_url,
            ],
            'products' => $products,
            'posts'    => $posts,
        ]);
    }
}
