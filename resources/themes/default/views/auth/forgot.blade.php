<x-crudPackage::layouts.guest
    title="Şifremi unuttum"
    heading="Şifremi unuttum"
    subtitle="Kayıtlı e-posta adresinizi girin, sıfırlama bağlantısını gönderelim."
    :back="route('login')"
>
    <form method="POST" action="{{ route('auth.password.forgot.send') }}" class="flex flex-col gap-4">
        @csrf

        <x-crudPackage::form.input
            name="email"
            type="email"
            label="E-posta"
            size="auth"
            placeholder="ornek@firma.com"
            :value="old('email')"
            required
            autofocus
        />

        <x-crudPackage::ui.button type="submit" size="lg" class="w-full mt-2" trailing="arrow">Bağlantıyı gönder</x-crudPackage::ui.button>

        <div class="mt-6 bg-app rounded-card px-6 py-5 flex gap-4 items-start">
            <span class="w-11 h-11 shrink-0 rounded-full border-[3px] border-accent flex items-center justify-center">
                <span class="w-3.5 h-3.5 rounded-full bg-accent"></span>
            </span>

            <span>
                <span class="block font-label text-[15px] font-semibold">Bağlantı {{ config('auth.passwords.' . config('auth.defaults.passwords') . '.expire', 60) }} dakika geçerlidir</span>
                <span class="block text-[15px] leading-normal text-body mt-1">Süre dolarsa aynı ekrandan yeni bağlantı talep edebilirsiniz.</span>
            </span>
        </div>
    </form>
</x-crudPackage::layouts.guest>
