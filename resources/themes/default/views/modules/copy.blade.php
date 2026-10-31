<x-crudPackage::layouts.app
    :title="$crud->display_single . ' kopyala'"
    :breadcrumb="[['label' => $crud->title, 'url' => route($crud->slug . '.index')], ['label' => 'Kopyala']]"
>
    @include('crudPackage::modules.form', ['copying' => true])
</x-crudPackage::layouts.app>
