<x-crudPackage::layouts.app
    :title="$definition['title'] ?? $key"
    :breadcrumb="[['label' => 'E-posta Şablonları', 'url' => route('mail-templates.index')], ['label' => 'Önizleme']]"
>
    <div class="max-w-[760px]">
        <x-crudPackage::ui.card :title="$definition['title'] ?? $key" subtitle="E-posta önizlemesi">
            @include('crudPackage::mailTemplates.preview')
        </x-crudPackage::ui.card>
    </div>
</x-crudPackage::layouts.app>
