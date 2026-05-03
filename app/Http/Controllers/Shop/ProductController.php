<?php

namespace App\Http\Controllers\Shop;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ProductController extends Controller
{
    public function index(Request $request): Response
    {
        $products = Product::with(['variations', 'media', 'category'])
            ->when($request->search, fn ($q, $search) => $q->where('name', 'like', "%{$search}%"))
            ->when($request->category, fn ($q, $slug) => $q->whereHas('category', fn ($q) => $q->where('slug', $slug)))
            ->when($request->min_price, fn ($q, $min) => $q->whereHas('variations', fn ($q) => $q->where('price', '>=', $min)))
            ->when($request->max_price, fn ($q, $max) => $q->whereHas('variations', fn ($q) => $q->where('price', '<=', $max)))
            ->paginate(24)
            ->withQueryString();

        return Inertia::render('Shop/Index', [
            'products'   => $products,
            'categories' => Category::all(),
            'filters'    => $request->only(['search', 'category', 'min_price', 'max_price']),
        ]);
    }

    public function show(Product $product): Response
    {
        $product->load(['variations', 'media', 'category']);

        return Inertia::render('Shop/Show', [
            'product'  => $product,
            'related'  => Product::with(['variations', 'media'])
                ->where('category_id', $product->category_id)
                ->where('id', '!=', $product->id)
                ->take(4)
                ->get(),
        ]);
    }
}
