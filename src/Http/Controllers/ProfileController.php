<?php

namespace crudPackage\Http\Controllers;

use crudPackage\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class ProfileController extends Controller
{
    public function edit()
    {
        $value = Auth::user();

        return view('crudPackage::profile.edit', compact('value'));
    }

    public function update(Request $request)
    {
        try
        {
            $user = User::find(Auth::id());

            $attribute =
                [
                    'name'             => 'Ad & Soyad',
                    'current_password' => 'Mevcut Şifre',
                    'password'         => 'Yeni Şifre',
                ];

            $rules =
                [
                    'name'             => 'required',
                    'current_password' => 'required_with:password',
                    'password'         => 'nullable|min:8|confirmed',
                ];

            $validator = Validator::make($request->all(), $rules);
            $validator->setAttributeNames($attribute);

            if ($validator->fails())
            {
                return response()->json(
                    [
                        'result'  => 2,
                        'message' => $validator->errors()
                    ],403
                );
            }

            if ($request->filled('password'))
            {
                if (!Hash::check($request->get('current_password'), $user->password))
                {
                    return response()->json(
                        [
                            'result'  => 2,
                            'message' => ['current_password' => ['Mevcut şifreniz hatalı.']]
                        ],403
                    );
                }

                $user->password = Hash::make($request->get('password'));
            }

            $user->name = $request->get('name');
            $user->save();

            return response()->json(
                [
                    'result'  => 1,
                    'message' => 'İşlem Başarılı.',
                    'route'   => route('profile.edit')
                ]
            );

        }
        catch (\Exception $e)
        {
            return response()->json(
                [
                    'result'  => 0,
                    'message' => 'İşleminizi şimdi gerçekleştiremiyoruz. Daha sonra tekrar deneyiniz.'
                ],403);
        }
    }
}
