<x-crudPackage::layouts.app
    :title="$user->name"
    :breadcrumb="[['label' => 'Kullanıcılar', 'url' => route('users.index')], ['label' => $user->name]]"
>
    @php
        $meta = [
            $user->email,
            $user->created_at ? $user->created_at->translatedFormat('d.m.Y') . ' tarihinden beri' : null,
        ];
    @endphp

    <div class="flex flex-col gap-3.5">

        <x-crudPackage::ui.detail-header
            :title="$user->name"
            :status="$user->status == 1 ? 'Aktif' : 'Pasif'"
            :status-tone="$user->status == 1 ? 'success' : 'muted'"
            :meta="$meta"
        >
            <x-slot:actions>
                @if (\crudPackage\Support\Permissions::allows('users.edit'))
                    <x-crudPackage::ui.button
                        x-data
                        @click="$dispatch('form-drawer', { url: {{ \Illuminate\Support\Js::from(route('users.edit', $user)) }}, title: 'Kullanıcıyı düzenle' })"
                        variant="outline"
                        size="sm"
                        icon="pen"
                    >
                        Düzenle
                    </x-crudPackage::ui.button>
                @endif
            </x-slot:actions>
        </x-crudPackage::ui.detail-header>

        <div class="grid lg:grid-cols-[1fr_380px] gap-3.5 items-start">
            <x-crudPackage::ui.card title="Kullanıcı bilgileri" flush>
                <div class="px-[22px] py-2">
                    <x-crudPackage::ui.detail-row label="Ad soyad">{{ $user->name }}</x-crudPackage::ui.detail-row>
                    <x-crudPackage::ui.detail-row label="E-posta">{{ $user->email }}</x-crudPackage::ui.detail-row>
                    <x-crudPackage::ui.detail-row label="Yetki">
                        <x-crudPackage::ui.badge :tone="$user->role_group_id == 1 ? 'primary' : 'accent'">{{ $user->roleGroup?->title ?? 'Yetkisiz' }}</x-crudPackage::ui.badge>
                    </x-crudPackage::ui.detail-row>
                    <x-crudPackage::ui.detail-row label="Durum">
                        <x-crudPackage::ui.status :tone="$user->status == 1 ? 'success' : 'muted'">{{ $user->status == 1 ? 'Aktif' : 'Pasif' }}</x-crudPackage::ui.status>
                    </x-crudPackage::ui.detail-row>
                    <x-crudPackage::ui.detail-row label="Kayıt tarihi">{{ $user->created_at?->translatedFormat('d F Y H:i') }}</x-crudPackage::ui.detail-row>
                    <x-crudPackage::ui.detail-row label="Son güncelleme">{{ $user->updated_at?->translatedFormat('d F Y H:i') }}</x-crudPackage::ui.detail-row>
                </div>
            </x-crudPackage::ui.card>
        </div>

    </div>
</x-crudPackage::layouts.app>
