<form method="POST" action="{{ $menu->exists ? route('menus.update', $menu) : route('menus.store') }}" class="flex flex-col gap-5">
    @csrf
    @if ($menu->exists)
        @method('PUT')
    @endif

    <x-crudPackage::form.input name="title" label="Başlık" :value="old('title', $menu->title)" required />

    @if (! $menu->exists)
        <x-crudPackage::ui.alert tone="outline" icon="menu">
            Menü oluşturulduktan sonra detay sayfasından öğe ekleyebilir ve sıralayabilirsiniz.
        </x-crudPackage::ui.alert>
    @endif

    <div class="-mx-5 lg:-mx-8 mt-1 px-5 lg:px-8 pt-4 lg:pt-5 border-t border-line-soft flex items-center gap-3">
        <x-crudPackage::ui.cancel-button :href="route('menus.index')" size="field" />
        <x-crudPackage::ui.button type="submit" size="field" trailing="arrow" class="flex-1">Kaydet</x-crudPackage::ui.button>
    </div>
</form>
