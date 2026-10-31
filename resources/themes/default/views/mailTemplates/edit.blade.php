<x-crudPackage::layouts.app
    :title="$definition['title'] ?? $key"
    :breadcrumb="[['label' => 'E-posta Şablonları', 'url' => route('mail-templates.index')], ['label' => 'Düzenle']]"
>
    <div class="max-w-[760px]">
        <x-crudPackage::ui.card :title="$definition['title'] ?? $key" subtitle="Konu, metin ve buton yazısını düzenleyin">
            @include('crudPackage::mailTemplates.form')
        </x-crudPackage::ui.card>
    </div>
</x-crudPackage::layouts.app>
