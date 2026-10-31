@props([
    'name'        => null,
    'label'       => null,
    'value'       => null,
    'required'    => false,
    'hint'        => null,
    'placeholder' => null,
    'sample'      => null,
])

@php
    $error = $name ? $errors->first(crudFieldKey($name)) : null;
@endphp

<x-crudPackage::form.field :label="$label" :name="$name" :required="$required" :hint="$hint">
    <div x-data="jsonField" class="flex flex-col gap-2.5">
        <x-crudPackage::form.box size="area" :error="$error" :disabled="$attributes->has('disabled')" multi ::class="invalid && '!border-danger !bg-tint-danger'">
            <textarea
                x-ref="input"
                @if ($name) name="{{ $name }}" id="{{ $name }}" @endif
                rows="1"
                spellcheck="false"
                placeholder="{{ $placeholder ?? $label }}"
                @if ($required) required @endif
                @input="resize(); invalid = false"
                @keydown.tab.prevent="indent()"
                {{ $attributes->class('flex-1 min-w-0 bg-transparent font-mono text-[13px] leading-relaxed text-ink placeholder:text-faint outline-none resize-none overflow-hidden') }}
            >{{ $value ?? ($name ? old($name) : null) }}</textarea>
        </x-crudPackage::form.box>

        <div class="flex flex-wrap items-center gap-3">
            <x-crudPackage::ui.button variant="outline" size="xs" icon="pen" @click="format(true)">JSON'u düzenle</x-crudPackage::ui.button>

            @if ($sample)
                <a href="{{ $sample }}" target="_blank" rel="noopener" class="text-sm text-primary hover:underline">Örnek JSON</a>
            @endif

            <span x-show="invalid" x-cloak class="text-xs text-danger">Hatalı JSON, düzenlenemiyor.</span>
        </div>
    </div>
</x-crudPackage::form.field>
