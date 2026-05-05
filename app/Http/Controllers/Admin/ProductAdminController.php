<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreProductRequest;
use App\Http\Requests\Admin\UpdateProductRequest;
use App\Http\Resources\ProductResource;
use App\Models\Category;
use App\Models\Product;
use App\Models\Tag;
use App\Services\StockService;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class ProductAdminController extends Controller
{
    public function __construct(private readonly StockService $stockService) {}

    public function index(): Response
    {
        return Inertia::render('Admin/Products/Index', [
            'products' => Product::with(['category', 'variations'])->get(),
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Admin/Products/Create', [
            'categories' => Category::all(),
            'allTags'    => Tag::ordered()->get(['id', 'name', 'color']),
        ]);
    }

    public function store(StoreProductRequest $request): RedirectResponse
    {
        $product = Product::create($request->safe()->except(['image', 'variations', 'tag_ids']));

        if ($request->hasFile('image')) {
            $product->addMediaFromRequest('image')->toMediaCollection('cover');
        }

        foreach ($request->validated('variations', []) as $variation) {
            $product->variations()->create($variation);
        }

        $product->syncTagIds($request->validated('tag_ids', []));

        return redirect()->route('admin.products.index')
            ->with('success', 'Product created successfully.');
    }

    public function edit(Product $product): Response
    {
        return Inertia::render('Admin/Products/Edit', [
            'product'    => new ProductResource($product->load(['category', 'media', 'variations', 'tags'])),
            'categories' => Category::all(),
            'allTags'    => Tag::ordered()->get(['id', 'name', 'color']),
        ]);
    }

    public function update(UpdateProductRequest $request, Product $product): RedirectResponse
    {
        $product->update($request->safe()->except(['image', 'variations', 'tag_ids']));

        if ($request->hasFile('image')) {
            $product->clearMediaCollection('cover');
            $product->addMediaFromRequest('image')->toMediaCollection('cover');
        }

        $product->syncTagIds($request->validated('tag_ids', []));

        return redirect()->route('admin.products.index')
            ->with('success', 'Product updated successfully.');
    }

    public function destroy(Product $product): RedirectResponse
    {
        $product->delete();

        return redirect()->route('admin.products.index')
            ->with('success', 'Product deleted successfully.');
    }
}
