<x-crudPackage::layouts.guest
    title="Giriş"
    eyebrow="Yetkili girişi"
    heading="Panele giriş"
    subtitle="Hesabınızla devam edin."
>
    <form method="POST" action="{{ route('auth.login.store') }}" class="flex flex-col gap-4">
        @csrf

        <x-crudPackage::form.input
            name="email"
            type="email"
            label="E-posta"
            size="auth"
            placeholder="ornek@firma.com"
            required
            autofocus
        />

        <x-crudPackage::form.password
            name="password"
            label="Şifre"
            size="auth"
            placeholder="••••••••"
            required
        />

        <div class="flex items-center justify-between gap-4 mt-1">
            <x-crudPackage::form.checkbox name="remember" label="Beni hatırla" />

            <a href="{{ route('auth.password.forgot') }}" class="text-base text-primary hover:opacity-80">Şifremi unuttum</a>
        </div>

        <x-crudPackage::ui.button type="submit" size="lg" class="w-full mt-2">Giriş yap</x-crudPackage::ui.button>
    </form>
</x-crudPackage::layouts.guest>
