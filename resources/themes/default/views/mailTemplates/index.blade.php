<x-crudPackage::layouts.app
    title="E-posta Şablonları"
    :breadcrumb="[['label' => 'Yönetim'], ['label' => 'E-posta Şablonları']]"
>
    <div class="flex flex-col gap-3.5">

        <x-crudPackage::ui.datatable
            heading="E-posta Şablonları"
            :subheading="$templates->count() . ' şablon · ' . $templates->where('custom', true)->count() . ' düzenlendi'"
            :columns="$columns"
            :rows="$templates"
            :export-url="false"
            record-label="şablonu"
            unit="şablondan"
            empty-title="Şablon yok"
            empty-message="Pakette tanımlı e-posta şablonu bulunamadı."
        />

    </div>
</x-crudPackage::layouts.app>
