<?php

namespace App\Http\Controllers\Shop;

use App\Http\Controllers\Controller;
use App\Models\BlogCategory;
use App\Models\BlogPost;
use App\Models\Setting;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class BlogController extends Controller
{
    public function index(Request $request): Response
    {
        $this->ensureBlogEnabled();

        $featured = BlogPost::published()
            ->with(['category:id,name,slug,color', 'author:id,name', 'media'])
            ->orderByDesc('published_at')
            ->take(3)
            ->get();

        $featuredIds = $featured->pluck('id')->all();

        $posts = BlogPost::published()
            ->with(['category:id,name,slug,color', 'author:id,name', 'media'])
            ->when($request->filled('category'), fn ($q) =>
                $q->whereHas('category', fn ($c) => $c->where('slug', $request->category))
            )
            ->whereNotIn('id', $featuredIds)
            ->orderByDesc('published_at')
            ->paginate(9)
            ->withQueryString();

        return Inertia::render('Shop/Blog/Index', [
            'featured'   => $featured,
            'posts'      => $posts,
            'categories' => BlogCategory::orderBy('name')->get(),
            'filter'     => $request->only('category'),
        ]);
    }

    public function show(string $slug): Response
    {
        $this->ensureBlogEnabled();

        $post = BlogPost::published()
            ->with(['category:id,name,slug,color', 'author:id,name', 'media'])
            ->where('slug', $slug)
            ->firstOrFail();

        $related = BlogPost::published()
            ->with(['category:id,name,slug,color', 'media'])
            ->where('id', '!=', $post->id)
            ->when($post->blog_category_id, fn ($q) => $q->where('blog_category_id', $post->blog_category_id))
            ->orderByDesc('published_at')
            ->take(3)
            ->get();

        return Inertia::render('Shop/Blog/Show', [
            'post'    => $post,
            'related' => $related,
        ]);
    }

    private function ensureBlogEnabled(): void
    {
        if (! Setting::first()?->blog_enabled) {
            throw new NotFoundHttpException();
        }
    }
}
