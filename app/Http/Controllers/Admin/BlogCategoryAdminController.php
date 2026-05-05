<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BlogCategory;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class BlogCategoryAdminController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('Admin/Blog/Categories/Index', [
            'categories' => BlogCategory::withCount('posts')->orderBy('name')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name'  => ['required', 'string', 'max:120'],
            'color' => ['required', 'string', 'regex:/^#[0-9A-Fa-f]{6}$/'],
        ]);
        $data['slug'] = $this->uniqueSlug($data['name']);

        BlogCategory::create($data);

        return back()->with('success', 'Categoria criada.');
    }

    public function update(Request $request, BlogCategory $category): RedirectResponse
    {
        $data = $request->validate([
            'name'  => ['required', 'string', 'max:120'],
            'color' => ['required', 'string', 'regex:/^#[0-9A-Fa-f]{6}$/'],
        ]);

        if ($data['name'] !== $category->name) {
            $data['slug'] = $this->uniqueSlug($data['name'], $category->id);
        }

        $category->update($data);

        return back()->with('success', 'Categoria atualizada.');
    }

    public function destroy(BlogCategory $category): RedirectResponse
    {
        $category->delete();

        return back()->with('success', 'Categoria removida.');
    }

    private function uniqueSlug(string $name, ?string $ignoreId = null): string
    {
        $base = Str::slug($name);
        $slug = $base;
        $i = 1;

        while (BlogCategory::where('slug', $slug)
            ->when($ignoreId, fn ($q) => $q->where('id', '!=', $ignoreId))
            ->exists()
        ) {
            $slug = $base . '-' . (++$i);
        }

        return $slug;
    }
}
