<form method="POST" action="{{ $item->exists ? route('menus.items.update', $item) : route('menus.items.store', $menu) }}" class="flex flex-col gap-5">
    @csrf
    @if ($item->exists)
        @method('PUT')
    @endif

    <x-crudPackage::form.select
        name="crud_id"
        label="Ekran"
        :options="$cruds"
        :selected="old('crud_id', $crudId ?? null)"
        placeholder="Ekran seçin"
        hint="Bir ekran seçerseniz başlık boşsa ekran adı, adres ise ekranın listesi olur."
    />

    <div class="grid sm:grid-cols-2 gap-4">
        <x-crudPackage::form.input name="title" label="Başlık" :value="old('title', $item->title)" />
        <x-crudPackage::form.input name="route" label="Url / Route" :value="old('route', $item->route)" placeholder="ornek.index veya https://..." />
        <x-crudPackage::form.select
            name="icon"
            label="İkon"
            :options="$icons"
            :selected="old('icon', \crudPackage\Support\Menu::resolveIcon($item->icon))"
            placeholder="Varsayılan ikon"
            icons
        />
        <x-crudPackage::form.select
            name="parent_id"
            label="Üst öğe"
            :options="$parents"
            :selected="old('parent_id', $item->parent_id ?: null)"
            placeholder="Üst öğe yok"
        />
    </div>

    <div class="grid sm:grid-cols-3 gap-4">
        <x-crudPackage::form.field label="Dinamik route">
            <x-crudPackage::form.toggle name="dynamic_route" on-label="Route adı" off-label="Url" :checked="(bool) old('dynamic_route', $item->dynamic_route ?? 1)" />
        </x-crudPackage::form.field>

        <x-crudPackage::form.field label="Yeni sekme">
            <x-crudPackage::form.toggle name="target" on-label="Açık" off-label="Kapalı" :checked="(bool) old('target', $item->target ?? 0)" />
        </x-crudPackage::form.field>

        <x-crudPackage::form.field label="Özel link">
            <x-crudPackage::form.toggle name="special" on-label="Evet" off-label="Hayır" :checked="(bool) old('special', $item->special ?? 0)" />
        </x-crudPackage::form.field>
    </div>

    <div class="-mx-5 lg:-mx-8 mt-1 px-5 lg:px-8 pt-4 lg:pt-5 border-t border-line-soft flex items-center gap-3">
        <x-crudPackage::ui.cancel-button :href="route('menus.show', $menu)" size="field" />
        <x-crudPackage::ui.button type="submit" size="field" trailing="arrow" class="flex-1">Kaydet</x-crudPackage::ui.button>
    </div>
</form>
