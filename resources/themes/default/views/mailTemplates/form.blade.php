<form method="POST" action="{{ route('mail-templates.update', $key) }}" class="flex flex-col gap-5">
    @csrf
    @method('PUT')

    <x-crudPackage::ui.alert tone="outline" icon="info">
        Kullanılabilir değişkenler:
        {!! collect($definition['variables'] ?? [])->map(fn ($variable, $name) => '<strong>{' . e($name) . '}</strong> ' . e($variable['label']))->implode(', ') !!}.
        Bağlantı butona otomatik eklenir.
    </x-crudPackage::ui.alert>

    <x-crudPackage::form.input name="subject" label="Konu" :value="old('subject', $fields['subject'])" required />

    <x-crudPackage::form.input name="heading" label="Başlık" :value="old('heading', $fields['heading'])" />

    <x-crudPackage::form.editor name="content" label="Metin" :value="old('content', $fields['content'])" :height="220" />

    <x-crudPackage::form.input name="button" label="Buton yazısı" :value="old('button', $fields['button'])" required />

    <x-crudPackage::form.editor name="note" label="Not" :value="old('note', $fields['note'])" :height="140" hint="Butonun altındaki bilgi kutusunda görünür. Boş bırakılırsa kutu gösterilmez." />

    <div class="-mx-5 lg:-mx-8 mt-1 px-5 lg:px-8 pt-4 lg:pt-5 border-t border-line-soft flex items-center gap-3">
        <x-crudPackage::ui.cancel-button :href="route('mail-templates.index')" size="field" />
        <x-crudPackage::ui.button type="submit" size="field" trailing="arrow" class="flex-1">Kaydet</x-crudPackage::ui.button>
    </div>
</form>
