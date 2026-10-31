@php
    $locked = request()->filled('email');
    $email  = $locked ? request()->string('email')->toString() : old('email');
@endphp

<x-crudPackage::layouts.guest
    title="Yeni şifre belirle"
    eyebrow="Bağlantı doğrulandı"
    heading="Yeni şifre belirle"
    :subtitle="$email ? $email . ' için yeni bir şifre oluşturun.' : 'Hesabınız için yeni bir şifre oluşturun.'"
    :back="route('login')"
>
    <form method="POST" action="{{ route('auth.password.reset.update') }}" class="flex flex-col gap-4">
        @csrf

        <input type="hidden" name="token" value="{{ old('token', request()->string('token')->toString()) }}">

        <x-crudPackage::form.input
            name="email"
            type="email"
            label="E-posta"
            size="auth"
            placeholder="ornek@firma.com"
            :value="$email"
            :readonly="$locked"
            required
        />

        <x-crudPackage::form.password
            name="password"
            label="Yeni şifre"
            size="auth"
            placeholder="Yeni şifre"
            required
            strength
            autofocus
        />

        <x-crudPackage::form.password
            name="password_confirmation"
            label="Yeni şifre tekrar"
            size="auth"
            placeholder="Yeni şifre tekrar"
            required
        />

        <x-crudPackage::ui.button type="submit" size="lg" class="w-full mt-2">Şifreyi güncelle</x-crudPackage::ui.button>
    </form>
</x-crudPackage::layouts.guest>
