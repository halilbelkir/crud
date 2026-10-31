@php
    $email = old('email', $email);
@endphp

<x-crudPackage::layouts.guest
    title="Şifrenizi belirleyin"
    eyebrow="Hoş geldiniz"
    heading="Şifrenizi belirleyin"
    :subtitle="$email ? $email . ' hesabı için bir şifre oluşturun.' : 'Hesabınız için bir şifre oluşturun.'"
    tagline="Hesabınız hazır<br>tek adım kaldı"
    :back="route('login')"
>
    <form method="POST" action="{{ route('invite.store') }}" class="flex flex-col gap-4">
        @csrf

        <input type="hidden" name="token" value="{{ old('token', $token) }}">
        <input type="hidden" name="email" value="{{ $email }}">

        <x-crudPackage::form.password
            name="password"
            label="Şifre"
            size="auth"
            placeholder="Şifre"
            required
            strength
            autofocus
        />

        <x-crudPackage::form.password
            name="password_confirmation"
            label="Şifre tekrar"
            size="auth"
            placeholder="Şifre tekrar"
            required
        />

        @error('email')
            <span class="text-xs text-danger">{{ $message }}</span>
        @enderror

        <x-crudPackage::ui.button type="submit" size="lg" class="w-full mt-2">Şifremi belirle</x-crudPackage::ui.button>
    </form>
</x-crudPackage::layouts.guest>
