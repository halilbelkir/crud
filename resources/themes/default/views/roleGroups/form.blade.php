<form method="POST" action="{{ $roleGroup->exists ? route('role-groups.update', $roleGroup) : route('role-groups.store') }}" class="flex flex-col gap-5">
    @csrf
    @if ($roleGroup->exists)
        @method('PUT')
    @endif

    <x-crudPackage::form.input name="title" label="Başlık" :value="old('title', $roleGroup->title)" required />

    <div class="flex flex-col gap-5 border-t border-line-soft pt-5">
        @foreach ($groups as $group)
            <div
                x-data="{
                    all: false,
                    boxes() { return [...$refs.items.querySelectorAll('input[type=checkbox]')] },
                    sync() { this.all = this.boxes().every((box) => box.checked) },
                    toggle(state) { this.boxes().forEach((box) => box.checked = state); this.all = state },
                }"
                x-init="sync()"
            >
                <div class="flex items-center justify-between gap-3 mb-3">
                    <span class="label-caps">{{ $group['label'] }}</span>

                    <x-crudPackage::form.checkbox label="Hepsini seç" x-bind:checked="all" x-on:change.stop="toggle($event.target.checked)" />
                </div>

                <div x-ref="items" @change="sync()" class="grid sm:grid-cols-2 gap-3">
                    @foreach ($group['items'] as $item)
                        <x-crudPackage::form.checkbox
                            :name="$item['name']"
                            :label="$item['label']"
                            :checked="$item['checked']"
                        />
                    @endforeach
                </div>
            </div>
        @endforeach
    </div>

    <div class="-mx-5 lg:-mx-8 mt-1 px-5 lg:px-8 pt-4 lg:pt-5 border-t border-line-soft flex items-center gap-3">
        <x-crudPackage::ui.cancel-button :href="route('role-groups.index')" size="field" />
        <x-crudPackage::ui.button type="submit" size="field" trailing="arrow" class="flex-1">Kaydet</x-crudPackage::ui.button>
    </div>
</form>
