<x-crudPackage::layouts.app
    title="Modüller"
    :breadcrumb="[['label' => 'Yönetim'], ['label' => 'Modüller']]"
>
    <x-crudPackage::ui.datatable
        heading="Modüller"
        :subheading="$created . ' / ' . $total . ' tablo kurulu'"
        :columns="$columns"
        :rows="$rows"
        :search="['name' => 'q', 'placeholder' => 'Modül veya tablo ara']"
        record-label="modülü"
        empty-title="Tablo bulunamadı"
        empty-message="Veritabanında modül oluşturulabilecek bir tablo yok."
    />
</x-crudPackage::layouts.app>
