<?php

namespace App\Http\Controllers\Shop;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Inertia\Inertia;
use Inertia\Response;

class CategoryController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('Shop/Categories', [
            'categories' => Category::with('media')->get(),
        ]);
    }

    public function show(Category $category): Response
    {
        $products = $category->products()
            ->with(['variations', 'media'])
            ->paginate(24);

        return Inertia::render('Shop/Category', [
            'category' => $category->load('media'),
            'products' => $products,
        ]);
    }
}
