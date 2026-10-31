<x-crudPackage::layouts.app
    title="Profilim"
    :breadcrumb="[['label' => 'Profilim']]"
>
    <div class="grid lg:grid-cols-[320px_1fr] gap-3.5 items-start">

        <div class="bg-card border border-line rounded-card p-4 lg:p-6 flex flex-col">
            <div class="flex flex-col items-center text-center gap-2 lg:gap-3 pb-5 lg:pb-6">
                <span class="lg:hidden">
                    <x-crudPackage::ui.avatar :name="$user->name" :size="76" tone="primary" />
                </span>

                <span class="hidden lg:block">
                    <x-crudPackage::ui.avatar :name="$user->name" :size="100" tone="primary" />
                </span>

                <div>
                    <div class="font-heading text-xl lg:text-2xl leading-tight">{{ $user->name }}</div>

                    <x-crudPackage::ui.badge tone="primary" class="mt-1.5 lg:mt-2">{{ $user->roleGroup?->title ?? 'Yetkisiz' }}</x-crudPackage::ui.badge>
                </div>
            </div>

            <div class="border-t border-line-soft pt-5 flex flex-col gap-4">
                @php
                    $rows = [
                        ['icon' => 'mail',     'label' => 'E-posta', 'value' => $user->email],
                        ['icon' => 'calendar', 'label' => 'Üyelik',  'value' => $user->created_at?->translatedFormat('d F Y') ?? '—'],
                    ];
                @endphp

                @foreach ($rows as $row)
                    <div class="flex items-center gap-3 min-w-0">
                        <span class="w-9 h-9 shrink-0 rounded-xl bg-tint-accent-2 text-on-tint-accent flex items-center justify-center">
                            <x-crudPackage::ui.icon :name="$row['icon']" :size="17" />
                        </span>

                        <span class="min-w-0">
                            <span class="block text-xs text-muted">{{ $row['label'] }}</span>
                            <span class="block text-sm truncate">{{ $row['value'] }}</span>
                        </span>
                    </div>
                @endforeach
            </div>
        </div>

        <div class="flex flex-col gap-3.5">

            <x-crudPackage::ui.card title="Profil Bilgileri" subtitle="Ad ve hesap bilgileri">
                <form method="POST" action="{{ route('profile.update') }}" class="flex flex-col gap-5">
                    @csrf
                    @method('PUT')

                    <div class="grid md:grid-cols-2 gap-4">
                        <x-crudPackage::form.input name="name" label="Ad soyad" :value="old('name', $user->name)" required />

                        <x-crudPackage::form.field label="E-posta">
                            <div class="h-field rounded-field px-5 bg-app border border-line-2 flex items-center gap-3">
                                <span class="flex-1 min-w-0 text-[15px] text-muted truncate">{{ $user->email }}</span>
                                <span class="text-xs text-faint shrink-0">değiştirilemez</span>
                            </div>
                        </x-crudPackage::form.field>

                        <x-crudPackage::form.field label="Yetki">
                            <div class="h-field rounded-field px-5 bg-app border border-line-2 flex items-center gap-3">
                                <span class="flex-1 min-w-0 text-[15px] text-muted truncate">{{ $user->roleGroup?->title ?? 'Yetkisiz' }}</span>
                                <span class="text-xs text-faint shrink-0">değiştirilemez</span>
                            </div>
                        </x-crudPackage::form.field>
                    </div>

                    <div class="flex items-center gap-3">
                        <x-crudPackage::ui.button type="submit" icon="check">Kaydet</x-crudPackage::ui.button>
                    </div>
                </form>
            </x-crudPackage::ui.card>

            <x-crudPackage::ui.card title="Şifre Değiştir" subtitle="Hesabınızın giriş şifresi">
                <form method="POST" action="{{ route('profile.password') }}" class="flex flex-col gap-5">
                    @csrf
                    @method('PUT')

                    <div class="grid md:grid-cols-3 gap-4">
                        <x-crudPackage::form.password name="current_password" label="Mevcut şifre" required />
                        <x-crudPackage::form.password name="password" label="Yeni şifre" required strength />
                        <x-crudPackage::form.password name="password_confirmation" label="Yeni şifre tekrar" required />
                    </div>

                    <div class="flex items-center">
                        <x-crudPackage::ui.button type="submit" icon="lock">Şifreyi değiştir</x-crudPackage::ui.button>
                    </div>
                </form>
            </x-crudPackage::ui.card>

        </div>

    </div>
</x-crudPackage::layouts.app>
