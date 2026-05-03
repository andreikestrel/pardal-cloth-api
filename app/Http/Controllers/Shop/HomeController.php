<?php

namespace App\Http\Controllers\Shop;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Product;
use App\Models\Promotion;
use Inertia\Inertia;
use Inertia\Response;

class HomeController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('Shop/Home', [
            'featuredProducts' => Product::with(['variations', 'media', 'category'])
                ->whereHas('variations', fn ($q) => $q->where('stock', '>', 0))
                ->latest()
                ->take(8)
                ->get(),
            'categories'       => Category::with('media')->get(),
            'activePromotions' => Promotion::where('active', true)
                ->where(fn ($q) => $q
                    ->whereNull('starts_at')->orWhere('starts_at', '<=', now()))
                ->where(fn ($q) => $q
                    ->whereNull('ends_at')->orWhere('ends_at', '>=', now()))
                ->orderBy('priority')
                ->get(),
        ]);
    }
}
