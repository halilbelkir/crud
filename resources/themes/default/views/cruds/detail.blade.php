<x-crudPackage::layouts.app
    :title="$crud->title"
    :breadcrumb="[['label' => 'Modüller', 'url' => route('cruds.index')], ['label' => $crud->title]]"
>
    @php
        $meta = [
            $crud->table_name,
            $items->total() . ' alan',
            $crud->created_at ? $crud->created_at->translatedFormat('d.m.Y') . ' tarihinden beri' : null,
        ];

        $area = json_decode($crud->area_1 ?? '{}', true) ?? [];

        $rows = [
            'Link'               => '/' . $crud->slug,
            'Tekli sayfa başlık' => $crud->display_single,
            'Çoklu sayfa başlık' => $crud->display_plural,
            'Model'              => $crud->model,
            'Sıralama'           => isset($area['order_column_name']) ? $area['order_column_name'] . ' (' . ($area['order_direction'] ?? 'asc') . ')' : null,
            'Sadece düzenleme'   => $crud->only_edit == 1 ? 'Evet' : 'Hayır',
            'Çok dilli'          => $crud->translatable == 1 ? 'Evet' : 'Hayır',
            'Açıklama'           => $crud->content,
        ];
    @endphp

    <div class="flex flex-col gap-3.5">

        <x-crudPackage::ui.detail-header
            :title="$crud->title"
            :status="$crud->status == 1 ? 'Aktif' : 'Pasif'"
            :status-tone="$crud->status == 1 ? 'success' : 'muted'"
            :meta="$meta"
        >
            <x-slot:actions>
                @if (\Illuminate\Support\Facades\Route::has($crud->slug . '.index') && $crud->only_edit != 1)
                    <x-crudPackage::ui.button :href="route($crud->slug . '.index')" variant="outline" size="sm" icon="link">Modüle git</x-crudPackage::ui.button>
                @endif

                @if (\crudPackage\Support\Permissions::allows('cruds.edit'))
                    <x-crudPackage::ui.button :href="route('cruds.edit', $crud)" variant="outline" size="sm" icon="pen">Düzenle</x-crudPackage::ui.button>
                @endif
            </x-slot:actions>
        </x-crudPackage::ui.detail-header>

        <x-crudPackage::ui.card title="Modül bilgileri" flush>
            <div class="px-[22px] py-2">
                @foreach ($rows as $label => $value)
                    <x-crudPackage::ui.detail-row :label="$label">{{ filled($value) ? $value : '—' }}</x-crudPackage::ui.detail-row>
                @endforeach
            </div>
        </x-crudPackage::ui.card>

        <x-crudPackage::ui.datatable
            heading="Modül alanları"
            subheading="Sürükleyerek form sırasını değiştirin"
            :columns="$columns"
            :rows="$items"
            :export-url="false"
            record-label="alanı"
            empty-title="Alan yok"
            empty-message="Bu modülde henüz alan tanımlanmamış."
        />

    </div>
</x-crudPackage::layouts.app>
