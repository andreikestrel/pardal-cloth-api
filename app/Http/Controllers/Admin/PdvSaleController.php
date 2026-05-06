<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\FinalizePdvSaleRequest;
use App\Models\CashSession;
use App\Models\Order;
use App\Services\PdvService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class PdvSaleController extends Controller
{
    public function __construct(private readonly PdvService $pdvService) {}

    public function terminal(Request $request): Response|\Illuminate\Http\RedirectResponse
    {
        $session = CashSession::where('operator_id', $request->user()->id)
            ->whereNull('closed_at')
            ->with(['cashRegister', 'operator'])
            ->first();

        if (! $session) {
            return redirect()->route('admin.pdv.index')
                ->with('error', 'Abra um caixa antes de acessar o terminal.');
        }

        return Inertia::render('Admin/PDV/Terminal', [
            'session' => [
                'id'       => $session->id,
                'register' => $session->cashRegister->name,
                'operator' => $session->operator->name,
                'close_url'=> route('admin.pdv.sessions.close', $session->id),
            ],
        ]);
    }

    public function products(Request $request): JsonResponse
    {
        $q = (string) $request->get('q', '');

        $results = $this->pdvService->searchProducts($q);

        return response()->json($results);
    }

    public function finalize(FinalizePdvSaleRequest $request): JsonResponse
    {
        $session = CashSession::where('operator_id', $request->user()->id)
            ->whereNull('closed_at')
            ->first();

        if (! $session) {
            return response()->json(['message' => 'Nenhum caixa aberto.'], 422);
        }

        $order = $this->pdvService->finalizeSale(
            $request->validated(),
            $session,
            $request->user()
        );

        return response()->json([
            'order_id'    => $order->id,
            'total'       => $order->total,
            'receipt_url' => route('admin.pdv.receipt', $order->id),
        ]);
    }

    public function receipt(Request $request, Order $order): \Symfony\Component\HttpFoundation\Response
    {
        // Only PDV orders generate receipts through this endpoint
        abort_if($order->source !== 'pdv', 404);

        $pdf = $this->pdvService->generateReceipt($order);

        return $pdf->download('recibo-' . substr($order->id, 0, 8) . '.pdf');
    }
}
