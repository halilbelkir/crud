<?php

namespace crudPackage\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class ProfileController extends Controller
{
    public function edit(Request $request): View
    {
        $user = $request->user()->loadMissing('roleGroup');

        return view('crudPackage::profile.index', compact('user'));
    }

    public function update(Request $request): RedirectResponse
    {
        $data = $request->validate(
        [
            'name' => ['required', 'string', 'max:255'],
        ],
        [],
        [
            'name' => 'Ad & Soyad',
        ]);

        $request->user()->forceFill($data)->save();

        return back()->with('status', 'Profil bilgileriniz güncellendi.');
    }

    public function password(Request $request): RedirectResponse
    {
        $user = $request->user();

        $request->validate(
        [
            'current_password' => ['required', 'string'],
            'password'         => ['required', 'string', 'min:8', 'confirmed'],
        ],
        [],
        [
            'current_password' => 'Mevcut şifre',
            'password'         => 'Yeni şifre',
        ]);

        if (! Hash::check($request->string('current_password')->toString(), $user->password))
        {
            throw ValidationException::withMessages(
            [
                'current_password' => 'Mevcut şifreniz hatalı.',
            ]);
        }

        $user->forceFill(['password' => Hash::make($request->string('password')->toString())])->save();

        return back()->with('status', 'Şifreniz değiştirildi.');
    }
}
