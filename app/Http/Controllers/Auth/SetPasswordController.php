<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\SetPasswordRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Inertia\Inertia;
use Inertia\Response;

class SetPasswordController extends Controller
{
    public function create(Request $request): Response
    {
        return Inertia::render('Auth/SetPassword', [
            'token' => $request->query('token'),
            'email' => $request->query('email'),
        ]);
    }

    public function store(SetPasswordRequest $request): RedirectResponse
    {
        $status = Password::reset(
            $request->validated(),
            function ($user, string $password) {
                $user->forceFill([
                    'password'          => Hash::make($password),
                    'email_verified_at' => $user->email_verified_at ?? now(),
                ])->save();

                Auth::login($user);
            }
        );

        if ($status !== Password::PASSWORD_RESET) {
            return back()->withErrors(['email' => __($status)]);
        }

        return redirect()->route('admin.dashboard')->with('success', 'Senha definida com sucesso.');
    }
}
