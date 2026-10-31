<x-crudPackage::layouts.app
    :title="$roleGroup->exists ? $roleGroup->title : 'Yeni yetki'"
    :breadcrumb="[['label' => 'Yetkiler', 'url' => route('role-groups.index')], ['label' => $roleGroup->exists ? 'Düzenle' : 'Yeni']]"
>
    <div class="max-w-[760px]">
        <x-crudPackage::ui.card title="Yetki bilgileri" subtitle="Modül bazlı erişim yetkileri">
            @include('crudPackage::roleGroups.form')
        </x-crudPackage::ui.card>
    </div>
</x-crudPackage::layouts.app>
