<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\InviteUserRequest;
use App\Models\Setting;
use App\Models\User;
use App\Notifications\InviteUserNotification;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;

class UserAdminController extends Controller
{
    public function index(): Response
    {
        $users = User::with('roles:id,name')
            ->orderByDesc('created_at')
            ->get(['id', 'name', 'email', 'created_at', 'email_verified_at'])
            ->map(fn (User $u) => [
                'id'         => $u->id,
                'name'       => $u->name,
                'email'      => $u->email,
                'role'       => $u->roles->first()?->name,
                'active'     => (bool) $u->email_verified_at,
                'created_at' => $u->created_at?->toDateTimeString(),
            ]);

        return Inertia::render('Admin/Users/Index', ['users' => $users]);
    }

    public function store(InviteUserRequest $request): RedirectResponse
    {
        $user = User::create([
            'name'     => $request->validated('name'),
            'email'    => $request->validated('email'),
            // Random unusable password — replaced when invitee sets their own
            'password' => Str::random(40),
        ]);

        $user->assignRole($request->validated('role'));

        $this->sendInvite($user);

        return back()->with('success', "Convite enviado para {$user->email}.");
    }

    public function resend(User $user): RedirectResponse
    {
        $this->sendInvite($user);

        return back()->with('success', "Convite reenviado para {$user->email}.");
    }

    public function destroy(User $user): RedirectResponse
    {
        if ($user->id === auth()->id()) {
            return back()->with('error', 'Você não pode remover seu próprio usuário.');
        }

        $user->delete();

        return back()->with('success', 'Usuário removido.');
    }

    private function sendInvite(User $user): void
    {
        // Password broker generates a hashed token in password_reset_tokens
        $token = Password::broker()->createToken($user);

        $companyName = Setting::first()?->company_name ?? config('app.name');

        $user->notify(new InviteUserNotification($token, $companyName));
    }
}
