@props([
    'name'      => null,
    'label'     => null,
    'value'     => null,
    'hint'      => null,
    'accept'    => 'image/jpeg,image/png,image/webp',
    'removable' => true,
])

@php
    $error = $name ? $errors->first(crudFieldKey($name)) : null;
@endphp

<x-crudPackage::form.field :label="$label" :name="$name" :hint="$hint">
    <div
        x-data="{ current: @js($value), preview: @js($value), file: '' }"
        class="flex items-center gap-4 rounded-[20px] border px-4 py-3 {{ $error ? 'bg-tint-danger border-danger' : 'bg-subtle border-line-2' }}"
    >
        <span class="w-14 h-14 shrink-0 rounded-2xl bg-card border border-line-soft flex items-center justify-center overflow-hidden">
            <template x-if="preview">
                <img :src="preview" alt="" class="w-full h-full object-contain">
            </template>

            <template x-if="! preview">
                <x-crudPackage::ui.icon name="file" :size="20" class="text-disabled" />
            </template>
        </span>

        <div class="min-w-0 flex-1 flex flex-col items-start gap-1.5">
            <label class="inline-flex items-center gap-2 h-8 rounded-2xl border border-line-2 bg-card px-3.5 font-label text-[12.5px] font-semibold text-ink cursor-pointer hover:border-line-3">
                <x-crudPackage::ui.icon name="upload" :size="14" />
                Görsel seç

                <input
                    type="file"
                    @if ($name) name="{{ $name }}" id="{{ $name }}" @endif
                    accept="{{ $accept }}"
                    class="sr-only"
                    @change="file = $el.files[0]?.name ?? ''; preview = $el.files[0] ? URL.createObjectURL($el.files[0]) : current"
                >
            </label>

            <span class="max-w-full text-xs text-muted truncate" x-text="file || (current ? 'Yüklü görsel korunur' : 'Görsel seçilmedi')"></span>
        </div>

        @if ($name && $removable)
            <span x-show="current && ! file" class="shrink-0">
                <x-crudPackage::form.checkbox size="sm" :name="$name . '_remove'" label="Kaldır" x-on:change="preview = $el.checked ? null : current" />
            </span>
        @endif
    </div>
</x-crudPackage::form.field>
