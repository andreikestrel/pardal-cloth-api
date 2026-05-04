<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StorePromotionRequest;
use App\Http\Requests\Admin\UpdatePromotionRequest;
use App\Models\Category;
use App\Models\Product;
use App\Models\Promotion;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class PromotionAdminController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('Admin/Promotions/Index', [
            'promotions' => Promotion::latest()->get(),
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Admin/Promotions/Create', [
            'categories' => Category::all(['id', 'name']),
            'products'   => Product::all(['id', 'name']),
        ]);
    }

    public function store(StorePromotionRequest $request): RedirectResponse
    {
        Promotion::create($request->validated());

        return redirect()->route('admin.promotions.index')
            ->with('success', 'Promotion created successfully.');
    }

    public function edit(Promotion $promotion): Response
    {
        return Inertia::render('Admin/Promotions/Edit', [
            'promotion'  => $promotion,
            'categories' => Category::all(['id', 'name']),
            'products'   => Product::all(['id', 'name']),
        ]);
    }

    public function update(UpdatePromotionRequest $request, Promotion $promotion): RedirectResponse
    {
        $promotion->update($request->validated());

        return redirect()->route('admin.promotions.index')
            ->with('success', 'Promotion updated successfully.');
    }

    public function destroy(Promotion $promotion): RedirectResponse
    {
        $promotion->delete();

        return redirect()->route('admin.promotions.index')
            ->with('success', 'Promotion deleted successfully.');
    }
}
