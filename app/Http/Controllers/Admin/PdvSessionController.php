<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\OpenCashSessionRequest;
use App\Models\CashRegister;
use App\Models\CashSession;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class PdvSessionController extends Controller
{
    public function index(Request $request): Response
    {
        $registers = CashRegister::where('active', true)
            ->with(['sessions' => fn ($q) => $q->whereNull('closed_at')->with('operator')])
            ->get()
            ->map(function (CashRegister $reg) {
                $active = $reg->sessions->first();

                return [
                    'id'             => $reg->id,
                    'name'           => $reg->name,
                    'location'       => $reg->location,
                    'active_session' => $active ? [
                        'id'       => $active->id,
                        'operator' => $active->operator->name,
                        'opened_at'=> $active->opened_at,
                    ] : null,
                ];
            });

        // Active session for the current user (so they can resume)
        $mySession = CashSession::where('operator_id', $request->user()->id)
            ->whereNull('closed_at')
            ->with('cashRegister')
            ->first();

        return Inertia::render('Admin/PDV/Index', [
            'registers' => $registers,
            'mySession' => $mySession ? [
                'id'       => $mySession->id,
                'register' => $mySession->cashRegister->name,
            ] : null,
        ]);
    }

    public function store(OpenCashSessionRequest $request): RedirectResponse
    {
        // One open session per operator at a time
        $existing = CashSession::where('operator_id', $request->user()->id)
            ->whereNull('closed_at')
            ->first();

        if ($existing) {
            return redirect()->route('admin.pdv.terminal');
        }

        CashSession::create([
            'cash_register_id' => $request->validated('cash_register_id'),
            'operator_id'      => $request->user()->id,
            'opened_at'        => now(),
            'opening_balance'  => $request->validated('opening_balance'),
        ]);

        return redirect()->route('admin.pdv.terminal');
    }

    public function close(Request $request, CashSession $session): Response|RedirectResponse
    {
        if ($session->operator_id !== $request->user()->id) {
            abort(403);
        }

        if (! $session->isOpen()) {
            return redirect()->route('admin.pdv.index');
        }

        // Compute expected balance: opening + cash sales in session
        $cashSalesTotal = $session->orders()
            ->whereHas('payment', fn ($q) => $q->where('method', 'cash')->where('status', 'approved'))
            ->join('payments', 'orders.id', '=', 'payments.order_id')
            ->sum('payments.amount');

        $expectedBalance = bcadd((string) $session->opening_balance, (string) $cashSalesTotal, 2);

        $summary = [
            'total_sales'      => $session->orders()->count(),
            'total_revenue'    => $session->orders()->sum('total'),
            'cash_sales'       => $cashSalesTotal,
            'expected_balance' => $expectedBalance,
        ];

        return Inertia::render('Admin/PDV/Close', [
            'session'  => [
                'id'              => $session->id,
                'register'        => $session->cashRegister->name,
                'operator'        => $session->operator->name,
                'opened_at'       => $session->opened_at,
                'opening_balance' => $session->opening_balance,
            ],
            'summary' => $summary,
        ]);
    }

    public function processClose(Request $request, CashSession $session): RedirectResponse
    {
        if ($session->operator_id !== $request->user()->id) {
            abort(403);
        }

        $request->validate([
            'closing_balance' => ['required', 'numeric', 'min:0'],
            'notes'           => ['nullable', 'string'],
        ]);

        $cashSalesTotal = $session->orders()
            ->whereHas('payment', fn ($q) => $q->where('method', 'cash')->where('status', 'approved'))
            ->join('payments', 'orders.id', '=', 'payments.order_id')
            ->sum('payments.amount');

        $session->update([
            'closed_at'        => now(),
            'closing_balance'  => $request->validated('closing_balance'),
            'expected_balance' => bcadd((string) $session->opening_balance, (string) $cashSalesTotal, 2),
            'notes'            => $request->input('notes'),
        ]);

        return redirect()->route('admin.pdv.index')->with('success', 'Caixa fechado com sucesso.');
    }
}
