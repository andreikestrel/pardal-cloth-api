<?php

namespace App\Http\Controllers\Shop;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Product;
use App\Models\Tag;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class ProductController extends Controller
{
    public function index(Request $request): Response
    {
        $query = Product::with(['variations', 'media', 'category']);

        // FULLTEXT search — uses MATCH ... AGAINST in BOOLEAN MODE for prefix support
        // (e.g. "cami*" matches "camiseta"). Falls back to LIKE for very short queries.
        $search = trim((string) $request->input('q', ''));
        if ($search !== '') {
            if (mb_strlen($search) < 3) {
                $query->where('name', 'like', "{$search}%");
            } else {
                $boolean = collect(preg_split('/\s+/', $search))
                    ->filter()
                    ->map(fn ($term) => '+' . $term . '*')
                    ->implode(' ');

                $query->whereRaw(
                    'MATCH(name, description) AGAINST (? IN BOOLEAN MODE)',
                    [$boolean]
                );
                // Order by relevance score
                $query->orderByRaw(
                    'MATCH(name, description) AGAINST (? IN BOOLEAN MODE) DESC',
                    [$boolean]
                );
            }
        }

        $categories = (array) $request->input('categories', []);
        if (!empty($categories)) {
            $query->whereHas('category', fn ($q) => $q->whereIn('slug', $categories));
        } elseif ($request->filled('category')) {
            $query->whereHas('category', fn ($q) => $q->where('slug', $request->category));
        }

        if ($request->filled('min_price')) {
            $min = (float) $request->input('min_price');
            $query->whereHas('variations', fn ($q) => $q->where('price', '>=', $min));
        }
        if ($request->filled('max_price')) {
            $max = (float) $request->input('max_price');
            $query->whereHas('variations', fn ($q) => $q->where('price', '<=', $max));
        }

        $sizes = (array) $request->input('sizes', []);
        if (!empty($sizes)) {
            $query->whereHas('variations', fn ($q) => $q->whereIn('size', $sizes));
        }

        $colors = (array) $request->input('colors', []);
        if (!empty($colors)) {
            $query->whereHas('variations', fn ($q) => $q->whereIn('color', $colors));
        }

        $tags = (array) $request->input('tags', []);
        if (!empty($tags)) {
            $query->whereHas('tags', fn ($q) => $q->whereIn('tags.id', $tags));
        }

        $products = $query->paginate(24)->withQueryString();

        // Aggregate distinct sizes/colors so the filters drawer shows real options
        $facets = [
            'sizes'  => DB::table('product_variations')->whereNotNull('size')->distinct()->orderBy('size')->pluck('size'),
            'colors' => DB::table('product_variations')->whereNotNull('color')->distinct()->orderBy('color')->pluck('color'),
            'tags'   => Tag::ordered()->get(['id', 'name', 'color']),
        ];

        return Inertia::render('Shop/Index', [
            'products'   => $products,
            'categories' => Category::all(),
            'facets'     => $facets,
            'filters'    => $request->only(['q', 'categories', 'category', 'min_price', 'max_price', 'sizes', 'colors', 'tags']),
        ]);
    }

    public function show(Product $product): Response
    {
        $product->load(['variations', 'media', 'category']);

        return Inertia::render('Shop/Show', [
            'product' => $product,
            'related' => Product::with(['variations', 'media'])
                ->where('category_id', $product->category_id)
                ->where('id', '!=', $product->id)
                ->take(4)
                ->get(),
        ]);
    }
}
