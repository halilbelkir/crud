<?php

namespace crudPackage\Http\Controllers;

use crudPackage\Models\User;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function index(Request $request)
    {
        if (!$request->ajax())
        {
            return $this->store($request);
        }

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
            return response(
                [
                    'response' => false,
                    'result'   => 4,
                    'message'  => 'Giriş Başarısız. Lütfen daha sonra tekrar deneyiniz.',
                    'exception' => $e
                ],403);
        }
    }

    public function store(Request $request): RedirectResponse
    {
        $credentials = $request->validate(
        [
            'email'    => ['required', 'email'],
            'password' => ['required', 'string'],
        ],
        [],
        [
            'email'    => 'E-Mail Adresi',
            'password' => 'Şifre',
        ]);

        if (!Auth::attempt($credentials, $request->boolean('remember')))
        {
            throw ValidationException::withMessages(
            [
                'email' => 'Giriş Başarısız. Lütfen E-mail veya şifrenizi kontrol ediniz.',
            ]);
        }

        if (Auth::user()->status != 1)
        {
            Auth::logout();

            throw ValidationException::withMessages(
            [
                'email' => 'Giriş Başarısız. Bu hesap pasife alınmıştır. Lütfen yöneticiniz ile kontrol ediniz.',
            ]);
        }

        $request->session()->regenerate();

        return redirect()->intended(route('dashboard'))->with('status', 'Hoş geldiniz, ' . Auth::user()->name . '.');
    }

    public function forgotSend(Request $request): RedirectResponse
    {
        $request->validate(
        [
            'email' => ['required', 'email'],
        ],
        [],
        [
            'email' => 'E-posta',
        ]);

        try
        {
            $status = Password::sendResetLink($request->only('email'));
        }
        catch (\Throwable $exception)
        {
            report($exception);

            throw ValidationException::withMessages(
            [
                'email' => 'İşleminizi şimdi gerçekleştiremiyoruz. Daha sonra tekrar deneyiniz.',
            ]);
        }

        if ($status === Password::RESET_THROTTLED)
        {
            throw ValidationException::withMessages(
            [
                'email' => 'Yakın zamanda şifre sıfırlama talebinde bulundunuz, lütfen e-postanızı kontrol edin.',
            ]);
        }

        if ($status !== Password::RESET_LINK_SENT)
        {
            throw ValidationException::withMessages(
            [
                'email' => 'Bu e-posta adresiyle kayıtlı bir hesap bulunamadı.',
            ]);
        }

        return back()->with('status', 'Sıfırlama bağlantısı e-posta adresinize gönderildi.');
    }

    public function passwordResetUpdate(Request $request): RedirectResponse
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

        $status = Password::reset($request->only('email', 'password', 'password_confirmation', 'token'), function (User $user, string $password)
        {
            $user->forceFill(
            [
                'password'              => Hash::make($password),
                'force_password_change' => 0,
            ])->setRememberToken(Str::random(60));
            $user->save();

            event(new PasswordReset($user));
        });

        if ($status !== Password::PASSWORD_RESET)
        {
            throw ValidationException::withMessages(
            [
                'email' => 'Sıfırlama bağlantısı geçersiz veya süresi dolmuş. Lütfen yeni bağlantı isteyin.',
            ]);
        }

        return redirect()->route('login')->with('status', 'Şifreniz güncellendi, yeni şifrenizle giriş yapabilirsiniz.');
    }
    public function logout()
    {
        Session::flush();

        Auth::logout();

        Cache::flush();

        return redirect()->route('login');
    }
}
