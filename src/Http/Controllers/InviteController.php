<?php

namespace crudPackage\Http\Controllers;

use crudPackage\Models\User;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class InviteController extends Controller
{
    public function create(Request $request, string $token): View
    {
        return view('crudPackage::auth.invite',
        [
            'token' => $token,
            'email' => $request->string('email')->toString(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $request->validate(
        [
            'token'    => ['required'],
            'email'    => ['required', 'email'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ],
        [],
        [
            'email'    => 'E-posta',
            'password' => 'Şifre',
        ]);

        $account = null;

        $status = Password::broker('invites')->reset($request->only('email', 'password', 'password_confirmation', 'token'), function (User $user, string $password) use (&$account)
        {
            $user->forceFill(
            [
                'password'              => Hash::make($password),
                'force_password_change' => 0,
            ])->setRememberToken(Str::random(60));

            $user->save();

            $account = $user;

            event(new PasswordReset($user));
        });

        if ($status !== Password::PASSWORD_RESET)
        {
            throw ValidationException::withMessages(
            [
                'email' => 'Davet bağlantısı geçersiz veya süresi dolmuş. Yöneticinizden yeni davet isteyin.',
            ]);
        }

        if ($account->status != 1)
        {
            return redirect()->route('login')->with('status', 'Şifreniz belirlendi. Hesabınız pasif durumda, yöneticinizle iletişime geçin.');
        }

        Auth::login($account);

        $request->session()->regenerate();

        return redirect()->route('dashboard')->with('status', 'Hoş geldiniz, ' . $account->name . '.');
    }
}
