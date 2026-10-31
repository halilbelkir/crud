<x-crudPackage::layouts.app
    title="Geçmiş"
    :breadcrumb="[['label' => 'Yönetim'], ['label' => 'Geçmiş']]"
>
    <x-crudPackage::ui.datatable
        heading="Geçmiş"
        :subheading="$total . ' kayıt'"
        :columns="$columns"
        :rows="$logs"
        :search="['name' => 'q', 'placeholder' => 'Bölüm, kayıt veya kullanıcı ara']"
        :filters="[
            ['name' => 'baslangic', 'label' => 'Başlangıç', 'type' => 'date'],
            ['name' => 'bitis',     'label' => 'Bitiş',     'type' => 'date'],
            ['name' => 'table',     'label' => 'Bölüm',     'options' => $tables],
            ['name' => 'event',     'label' => 'İşlem',     'options' => $events],
            ['name' => 'user',      'label' => 'Kullanıcı', 'options' => $users],
        ]"
        record-label="log kaydını"
        empty-title="Log bulunamadı"
        empty-message="Filtrelere uyan işlem kaydı yok. Filtreleri temizleyip tekrar deneyin."
    />
</x-crudPackage::layouts.app>
