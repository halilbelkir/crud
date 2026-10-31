<x-crudPackage::layouts.app
    :title="$roleGroup->title"
    :breadcrumb="[['label' => 'Yetkiler', 'url' => route('role-groups.index')], ['label' => $roleGroup->title]]"
>
    @php
        $admin   = (int) $roleGroup->getKey() === 1;
        $granted = collect($groups)->filter(fn ($group) => collect($group['items'])->contains('checked', true))->count();

        $meta = [
            $admin ? 'Tüm modüller' : $granted . ' modül',
            $roleGroup->users_count . ' kullanıcı',
            $roleGroup->created_at ? $roleGroup->created_at->translatedFormat('d.m.Y') . ' tarihinden beri' : null,
        ];
    @endphp

    <div class="flex flex-col gap-3.5">

        <x-crudPackage::ui.detail-header
            :title="$roleGroup->title"
            :status="$admin ? 'Tam yetki' : 'Kısıtlı'"
            :status-tone="$admin ? 'success' : 'muted'"
            :meta="$meta"
        >
            <x-slot:actions>
                @if (\crudPackage\Support\Permissions::allows('role-groups.edit'))
                    <x-crudPackage::ui.button
                        x-data
                        @click="$dispatch('form-drawer', { url: {{ \Illuminate\Support\Js::from(route('role-groups.edit', $roleGroup)) }}, title: 'Yetkiyi düzenle' })"
                        variant="outline"
                        size="sm"
                        icon="pen"
                    >
                        Düzenle
                    </x-crudPackage::ui.button>
                @endif
            </x-slot:actions>
        </x-crudPackage::ui.detail-header>

        <div class="grid lg:grid-cols-[380px_1fr] gap-3.5 items-start">
            <x-crudPackage::ui.card title="Yetki bilgileri" flush>
                <div class="px-[22px] py-2">
                    <x-crudPackage::ui.detail-row label="Başlık">{{ $roleGroup->title }}</x-crudPackage::ui.detail-row>
                    <x-crudPackage::ui.detail-row label="Modül">{{ $admin ? 'Tümü' : $granted }}</x-crudPackage::ui.detail-row>
                    <x-crudPackage::ui.detail-row label="Kullanıcı">{{ $roleGroup->users_count }}</x-crudPackage::ui.detail-row>
                    <x-crudPackage::ui.detail-row label="Tam yetki">
                        <x-crudPackage::ui.status :tone="$admin ? 'success' : 'muted'">{{ $admin ? 'Evet' : 'Hayır' }}</x-crudPackage::ui.status>
                    </x-crudPackage::ui.detail-row>
                </div>
            </x-crudPackage::ui.card>

            <x-crudPackage::ui.card title="İzinler" subtitle="Modül bazlı erişim yetkileri" flush>
                <div class="px-[22px] py-4">
                    <div class="flex flex-col gap-4">
                        @foreach ($groups as $group)
                            <div>
                                <div class="label-caps mb-2.5">{{ $group['label'] }}</div>

                                <div class="flex flex-wrap gap-2">
                                    @foreach ($group['items'] as $item)
                                        <x-crudPackage::ui.badge :tone="$admin || $item['checked'] ? 'success' : 'neutral'">
                                            {{ $item['label'] }}
                                        </x-crudPackage::ui.badge>
                                    @endforeach
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </x-crudPackage::ui.card>
        </div>

    </div>
</x-crudPackage::layouts.app>
