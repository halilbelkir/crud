<?php

namespace crudPackage\Http\Controllers;

use crudPackage\Models\User;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Session;
use Validator;

class AuthController extends Controller
{
    public function index(Request $request)
    {
        try
        {
            $attribute =
                [
                    'email'    => 'E-Mail Adresi',
                    'password' => 'Şifre'
                ];

            $rules =
                [
                    'email'    => 'required|email',
                    'password' => 'required',
                ];

            $validator = Validator::make($request->all(), $rules);
            $validator->setAttributeNames($attribute);

            if ($validator->fails())
            {
                return response()->json(
                    [
                        'result' => 2,
                        'message' => $validator->errors()
                    ],403
                );
            }

            $email    = $request->get('email');
            $password = $request->get('password');
            $user     = User::where('email', $email)->first();

            if (!$user)
            {
                return response(
                    [
                        'response' => false,
                        'result'   => 3,
                        'message'  => 'Giriş Başarısız. Lütfen E-mail veya şifrenizi kontrol ediniz.'
                    ],403);
            }

            if ($user->status == 1)
            {
                $credentials = ['email' => $email, 'password' => $password];

                if (Auth::attempt($credentials))
                {
                    $request->session()->regenerate();

                    return response()->json(
                        [
                            'result'  => 1,
                            'message' => 'Giriş Başarılı. Yönlendiriliyorsunuz...',
                            'route'   => route('dashboard')
                        ]
                    );
                }
                else
                {
                    return response(
                        [
                            'response' => false,
                            'result'   => 3,
                            'message'  => 'Giriş Başarısız. Lütfen E-mail veya şifrenizi kontrol ediniz.'
                        ],403);
                }
            }
            else
            {
                return response(
                    [
                        'response' => false,
                        'result'   => 3,
                        'message'  => 'Giriş Başarısız. Bu hesap pasife alınmıştır. Lütfen yöneticiniz ile kontrol ediniz.'
                    ],403);
            }
        }
        catch (\Exception $e)
        {
            dd($e);
            return response(
                [
                    'response' => false,
                    'result'   => 4,
                    'message'  => 'Giriş Başarısız. Lütfen daha sonra tekrar deneyiniz.',
                    'exception' => $e
                ],403);
        }
    }

    public function forgotSend(Request $request)
    {
        try
        {
            $attribute =
                [
                    'email'    => 'E-Mail',
                ];

            $rules =
                [
                    'email'    => 'email|required',
                ];

            $validator = Validator::make($request->all(), $rules);
            $validator->setAttributeNames($attribute);

            if ($validator->fails())
            {
                return response()->json(
                    [
                        'result' => 2,
                        'message' => $validator->errors()
                    ],403
                );
            }

            $status = Password::sendResetLink(
                $request->only('email')
            );

            if ($status === Password::RESET_LINK_SENT)
            {
                Password::sendResetLink(['email' => $request->email]);
                return response()->json(
                    [
                        'result'  => 1,
                        'message' => 'İşlem Başarılı. E-Mail adresinize şifre sıfırlama linki gönderilmiştir.',
                        'route'   => route('login')
                    ]
                );
            }
            else if($status === Password::RESET_THROTTLED)
            {
                return response()->json(
                    [
                        'result'  => 0,
                        'message' => 'Yakın zamanda şifre sıfırlama talebinde bulundunuz, lütfen e-postanızı kontrol edin.'
                    ],403);
            }
            else
            {
                return response()->json(
                    [
                        'result'  => 0,
                        'message' => 'İşlem Başarısız. Lütfen e-mail adresinizi kontrol ediniz.'
                    ],403);
            }
        }
        catch (\Exception $e)
        {
            dd($e);
            return response()->json(['result' => 0,'message' => 'İşleminizi şimdi gerçekleştiremiyoruz. Daha sonra tekrar deneyiniz.'],403);
        }
    }

    public function passwordResetUpdate(Request $request)
    {
        try
        {
            $attribute =
                [
                    'email'    => 'E-Mail',
                    'password' => 'Şifre',
                ];

            $rules =
                [
                    'email'    => 'email|required',
                    'password' => 'required|confirmed|min:8',
                ];

            $validator = Validator::make($request->all(), $rules);
            $validator->setAttributeNames($attribute);

            if ($validator->fails())
            {
                return response()->json(
                    [
                        'result' => 2,
                        'message' => $validator->errors()
                    ],403
                );
            }

            $status = Password::reset(
                $request->only('email', 'password', 'password_confirmation', 'token'),
                function (User $user, string $password) {
                    $user->forceFill([
                        'password' => Hash::make($password)
                    ])->setRememberToken(Str::random(60));

                    $user->save();

                    event(new PasswordReset($user));
                }
            );

            if ($status === Password::PASSWORD_RESET)
            {
                return response()->json(
                    [
                        'result'  => 1,
                        'message' => 'İşlem Başarılı. Yönlendiriliyorsunuz...',
                        'route'   => route('login')
                    ]
                );
            }
            else
            {
                return response()->json(
                    [
                        'result'  => 0,
                        'message' => 'İşlem Başarısız. Lütfen e-mail adresinizi kontrol ediniz.'
                    ],403);
            }
        }
        catch (\Exception $e)
        {
            dd($e);
            return response()->json(['result' => 0,'message' => 'İşleminizi şimdi gerçekleştiremiyoruz. Daha sonra tekrar deneyiniz.'],403);
        }
    }
    public function logout()
    {
        Session::flush();

        Auth::logout();

        Cache::flush();

        return redirect()->route('login');
    }
}
