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
                ->get(),
        ]);
    }

    public function adjust(AdjustStockRequest $request): RedirectResponse
    {
        $variation = ProductVariation::findOrFail($request->validated('variation_id'));

        $this->stockService->adjust(
            $variation,
            $request->validated('quantity'),
            $request->validated('reason'),
            $request->user(),
        );

        return back()->with('success', 'Estoque ajustado com sucesso.');
    }
}
