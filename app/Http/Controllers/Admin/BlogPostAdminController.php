<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreBlogPostRequest;
use App\Http\Requests\Admin\UpdateBlogPostRequest;
use App\Models\BlogCategory;
use App\Models\BlogPost;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;
use Mews\Purifier\Facades\Purifier;

class BlogPostAdminController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('Admin/Blog/Posts/Index', [
            'posts' => BlogPost::with(['category:id,name,color', 'author:id,name', 'media'])
                ->orderByDesc('created_at')
                ->get(),
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Admin/Blog/Posts/Form', [
            'post'       => null,
            'categories' => BlogCategory::orderBy('name')->get(),
        ]);
    }

    public function store(StoreBlogPostRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $data['slug']      = $this->uniqueSlug($data['title']);
        $data['author_id'] = $request->user()->id;
        $data['body_html'] = $this->sanitize($data['body_html']);

        $post = BlogPost::create(collect($data)->except(['cover'])->toArray());

        if ($request->hasFile('cover')) {
            $post->addMediaFromRequest('cover')->toMediaCollection('cover');
        }

        return redirect()->route('admin.blog.posts.edit', $post)
            ->with('success', 'Post salvo.');
    }

    public function edit(BlogPost $post): Response
    {
        $post->load(['category', 'author', 'media']);

        return Inertia::render('Admin/Blog/Posts/Form', [
            'post'       => $post,
            'categories' => BlogCategory::orderBy('name')->get(),
        ]);
    }

    public function update(UpdateBlogPostRequest $request, BlogPost $post): RedirectResponse
    {
        $data = $request->validated();

        if (isset($data['title']) && $data['title'] !== $post->title) {
            $data['slug'] = $this->uniqueSlug($data['title'], $post->id);
        }
        if (isset($data['body_html'])) {
            $data['body_html'] = $this->sanitize($data['body_html']);
        }

        $post->update(collect($data)->except(['cover'])->toArray());

        if ($request->hasFile('cover')) {
            $post->clearMediaCollection('cover');
            $post->addMediaFromRequest('cover')->toMediaCollection('cover');
        }

        return back()->with('success', 'Post atualizado.');
    }

    public function destroy(BlogPost $post): RedirectResponse
    {
        $post->delete();

        return redirect()->route('admin.blog.posts.index')->with('success', 'Post removido.');
    }

    public function uploadImage(Request $request)
    {
        $request->validate(['image' => ['required', 'image', 'max:8192']]);

        $path = $request->file('image')->store('blog-inline', 'public');

        return response()->json([
            'url' => Storage::disk('public')->url($path),
        ]);
    }

    /**
     * HTMLPurifier strips XSS while preserving the rich-text formatting Tiptap produces.
     * The 'rich' config (config/purifier.php) keeps img, links, headings, lists, etc.
     */
    private function sanitize(string $html): string
    {
        return Purifier::clean($html, 'rich');
    }

    private function uniqueSlug(string $title, ?string $ignoreId = null): string
    {
        $base = Str::slug($title);
        $slug = $base;
        $i = 1;

        while (BlogPost::where('slug', $slug)
            ->when($ignoreId, fn ($q) => $q->where('id', '!=', $ignoreId))
            ->exists()
        ) {
            $slug = $base . '-' . (++$i);
        }

        return $slug;
    }
}
