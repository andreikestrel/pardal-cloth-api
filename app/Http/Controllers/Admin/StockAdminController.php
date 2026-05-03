<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\AdjustStockRequest;
use App\Models\ProductVariation;
use App\Services\StockService;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class StockAdminController extends Controller
{
    public function __construct(private readonly StockService $stockService) {}

    public function index(): Response
    {
        return Inertia::render('Admin/Stock/Index', [
            'variations' => ProductVariation::with('product')
                ->orderBy('stock')
                ->paginate(50),
        ]);
    }

    public function adjust(AdjustStockRequest $request): RedirectResponse
    {
        $variation = ProductVariation::findOrFail($request->validated('variation_id'));

        $this->stockService->adjust(
            $variation,
            $request->validated('quantity'),
            $request->validated('reason'),
        );

        return back()->with('success', 'Stock adjusted successfully.');
    }
}
