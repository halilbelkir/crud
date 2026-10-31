<x-crudPackage::layouts.app
    title="Menüler"
    :breadcrumb="[['label' => 'Yönetim'], ['label' => 'Menüler']]"
>
    <x-crudPackage::ui.datatable
        heading="Menüler"
        :subheading="$total . ' menü'"
        :columns="$columns"
        :rows="$menus"
        :search="['name' => 'q', 'placeholder' => 'Menü ara']"
        :create="\crudPackage\Support\Permissions::allows('menus.create') ? ['label' => 'Yeni menü', 'href' => route('menus.create')] : null"
        record-label="menüyü"
        empty-title="Menü bulunamadı"
        empty-message="Henüz tanımlı bir menü yok. Yeni menü ekleyerek başlayın."
    />
</x-crudPackage::layouts.app>
