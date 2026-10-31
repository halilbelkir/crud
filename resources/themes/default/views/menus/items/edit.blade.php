<x-crudPackage::layouts.app
    :title="$item->exists ? $item->title : 'Yeni öğe'"
    :breadcrumb="[['label' => 'Menüler', 'url' => route('menus.index')], ['label' => $menu->title, 'url' => route('menus.show', $menu)], ['label' => $item->exists ? 'Düzenle' : 'Yeni öğe']]"
>
    <div class="max-w-[760px]">
        <x-crudPackage::ui.card title="Öğe bilgileri" subtitle="Menüde görünecek bağlantı">
            @include('crudPackage::menus.items.form')
        </x-crudPackage::ui.card>
    </div>
</x-crudPackage::layouts.app>
