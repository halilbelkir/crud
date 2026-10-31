<x-crudPackage::layouts.app
    :title="$crud->display_single . ' düzenle'"
    :breadcrumb="$crud->only_edit == 1
        ? [['label' => $crud->title]]
        : [['label' => $crud->title, 'url' => route($crud->slug . '.index')], ['label' => 'Düzenle']]"
>
    @include('crudPackage::modules.form')
</x-crudPackage::layouts.app>
