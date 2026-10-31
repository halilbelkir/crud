@props([
    'name'     => null,
    'label'    => null,
    'value'    => null,
    'required' => false,
    'hint'     => null,
    'fallback' => '#1C1A1B',
])

@php
    $error   = $name ? $errors->first(crudFieldKey($name)) : null;
    $current = $value ?? ($name ? old($name) : null);
@endphp

<x-crudPackage::form.field :label="$label" :name="$name" :required="$required" :hint="$hint">
    <label
        x-data="{
            color: @js($current ?: $fallback),
            get light()
            {
                const hex = this.color.replace('#', '');
                const red = parseInt(hex.slice(0, 2), 16);
                const green = parseInt(hex.slice(2, 4), 16);
                const blue = parseInt(hex.slice(4, 6), 16);

                return (red * 299 + green * 587 + blue * 114) / 1000 > 150;
            },
        }"
        class="{{ config('crudPanel.field.box.md') }} flex items-center justify-end cursor-pointer border {{ $error ? 'border-danger' : 'border-line-2' }}"
        :style="'background-color: ' + color"
    >
        <input
            type="color"
            @if ($name) name="{{ $name }}" id="{{ $name }}" @endif
            x-model="color"
            class="sr-only"
        >

        <span
            class="font-label text-xs font-semibold"
            :class="light ? 'text-ink' : 'text-white/80'"
            x-text="color"
        ></span>
    </label>
</x-crudPackage::form.field>
