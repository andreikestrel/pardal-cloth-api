<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Tag;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class TagAdminController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('Admin/Tags/Index', [
            'tags' => Tag::ordered()->get()->map(fn (Tag $tag) => [
                'id'       => $tag->id,
                'name'     => $tag->name,
                'slug'     => $tag->slug,
                'color'    => $tag->color,
                'link_url' => $tag->link_url,
            ]),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name'     => 'required|string|max:80|unique:tags,name',
            'color'    => 'required|string|regex:/^#[0-9a-fA-F]{6}$/',
            'link_url' => 'nullable|string|max:500',
        ]);

        Tag::create($data);

        return back();
    }

    public function update(Request $request, Tag $tag): RedirectResponse
    {
        $data = $request->validate([
            'name'     => "required|string|max:80|unique:tags,name,{$tag->id}",
            'color'    => 'required|string|regex:/^#[0-9a-fA-F]{6}$/',
            'link_url' => 'nullable|string|max:500',
        ]);

        $tag->update($data);

        return back();
    }

    public function destroy(Tag $tag): RedirectResponse
    {
        $tag->delete();

        return back();
    }
}
