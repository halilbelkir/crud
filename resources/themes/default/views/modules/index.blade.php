<x-crudPackage::layouts.app
    :title="$crud->title"
    :breadcrumb="[['label' => $crud->title]]"
>
    <x-crudPackage::ui.datatable
        :heading="$crud->title"
        :subheading="$total . ' kayıt'"
        :columns="$columns"
        :rows="$rows"
        :search="$search->isNotEmpty() ? ['name' => 'q', 'placeholder' => $search->pluck('title')->take(2)->implode(' veya ') . ' ara'] : null"
        :create="\crudPackage\Support\Permissions::allows($crud->slug . '.create') ? ['label' => 'Yeni ' . trLower($crud->display_single ?: 'kayıt'), 'href' => route($crud->slug . '.create')] : null"
        :record-label="'bu ' . trLower($crud->display_single ?: 'kaydı')"
        :empty-title="$crud->title . ' boş'"
        empty-message="Aramanıza uyan kayıt yok veya henüz kayıt eklenmemiş."
    />
</x-crudPackage::layouts.app>
