<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CashRegister;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class CashRegisterAdminController extends Controller
{
    public function index(): Response
    {
        $registers = CashRegister::orderBy('name')
            ->withCount('sessions')
            ->get()
            ->map(fn (CashRegister $r) => [
                'id'            => $r->id,
                'name'          => $r->name,
                'location'      => $r->location,
                'active'        => $r->active,
                'sessions_count'=> $r->sessions_count,
            ]);

        return Inertia::render('Admin/PDV/Registers', [
            'registers' => $registers,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name'     => ['required', 'string', 'max:100'],
            'location' => ['nullable', 'string', 'max:200'],
        ]);

        CashRegister::create([...$data, 'active' => true]);

        return back()->with('success', 'Caixa criado com sucesso.');
    }

    public function update(Request $request, CashRegister $register): RedirectResponse
    {
        $data = $request->validate([
            'name'     => ['required', 'string', 'max:100'],
            'location' => ['nullable', 'string', 'max:200'],
            'active'   => ['required', 'boolean'],
        ]);

        $register->update($data);

        return back()->with('success', 'Caixa atualizado.');
    }

    public function destroy(CashRegister $register): RedirectResponse
    {
        // Prevent deletion if any sessions exist — deactivate instead
        if ($register->sessions()->exists()) {
            return back()->withErrors(['register' => 'Este caixa tem sessões registradas e não pode ser excluído. Desative-o.']);
        }

        $register->delete();

        return back()->with('success', 'Caixa removido.');
    }
}
