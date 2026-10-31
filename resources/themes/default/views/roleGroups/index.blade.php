<x-crudPackage::layouts.app
    title="Yetkiler"
    :breadcrumb="[['label' => 'Yönetim'], ['label' => 'Yetkiler']]"
>
    <x-crudPackage::ui.datatable
        heading="Yetkiler"
        :subheading="$total . ' yetki · ' . $assigned . ' kullanımda'"
        :columns="$columns"
        :rows="$roleGroups"
        :search="['name' => 'q', 'placeholder' => 'Yetki ara']"
        :create="\crudPackage\Support\Permissions::allows('role-groups.create') ? ['label' => 'Yeni yetki', 'href' => route('role-groups.create')] : null"
        record-label="yetkiyi"
        empty-title="Yetki bulunamadı"
        empty-message="Henüz tanımlı bir yetki yok. Yeni yetki ekleyerek başlayın."
    />
</x-crudPackage::layouts.app>
