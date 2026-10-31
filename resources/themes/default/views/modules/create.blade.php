<x-crudPackage::layouts.app
    :title="'Yeni ' . trLower($crud->display_single ?: 'kayıt')"
    :breadcrumb="[['label' => $crud->title, 'url' => route($crud->slug . '.index')], ['label' => 'Yeni']]"
>
    @include('crudPackage::modules.form')
</x-crudPackage::layouts.app>
