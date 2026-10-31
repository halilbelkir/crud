<x-crudPackage::layouts.app
    :title="$menu->exists ? $menu->title : 'Yeni menü'"
    :breadcrumb="[['label' => 'Menüler', 'url' => route('menus.index')], ['label' => $menu->exists ? 'Düzenle' : 'Yeni']]"
>
    <div class="max-w-[760px]">
        <x-crudPackage::ui.card title="Menü bilgileri" subtitle="Menünün adı">
            @include('crudPackage::menus.form')
        </x-crudPackage::ui.card>
    </div>
</x-crudPackage::layouts.app>
