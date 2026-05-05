<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreHeroSlideRequest;
use App\Http\Requests\Admin\UpdateHeroSlideRequest;
use App\Models\HeroSlide;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class CatalogAdminController extends Controller
{
    public function slides(): Response
    {
        return Inertia::render('Admin/Catalog/Slides/Index', [
            'slides' => HeroSlide::with('media')
                ->orderBy('sort_order')
                ->orderByDesc('created_at')
                ->get(),
        ]);
    }

    public function storeSlide(StoreHeroSlideRequest $request): RedirectResponse
    {
        $slide = HeroSlide::create($request->safe()->except('image'));

        if ($request->hasFile('image')) {
            $slide->addMediaFromRequest('image')->toMediaCollection('image');
        }

        return back()->with('success', 'Slide criado.');
    }

    public function updateSlide(UpdateHeroSlideRequest $request, HeroSlide $slide): RedirectResponse
    {
        $slide->update($request->safe()->except('image'));

        if ($request->hasFile('image')) {
            $slide->clearMediaCollection('image');
            $slide->addMediaFromRequest('image')->toMediaCollection('image');
        }

        return back()->with('success', 'Slide atualizado.');
    }

    public function destroySlide(HeroSlide $slide): RedirectResponse
    {
        $slide->delete();

        return back()->with('success', 'Slide removido.');
    }

    public function reorderSlides(Request $request): RedirectResponse
    {
        $request->validate([
            'order'   => ['required', 'array'],
            'order.*' => ['uuid', 'exists:hero_slides,id'],
        ]);

        foreach ($request->input('order') as $index => $id) {
            HeroSlide::where('id', $id)->update(['sort_order' => $index]);
        }

        return back();
    }
}
