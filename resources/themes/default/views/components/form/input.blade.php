@props([
    'name'        => null,
    'label'       => null,
    'type'        => 'text',
    'value'       => null,
    'size'        => 'md',
    'required'    => false,
    'hint'        => null,
    'icon'        => null,
    'placeholder' => null,
])

@php
    $error = $name ? $errors->first(crudFieldKey($name)) : null;
@endphp

<x-crudPackage::form.field :label="$label" :name="$name" :required="$required" :hint="$hint">
    <x-crudPackage::form.box :size="$size" :error="$error" :disabled="$attributes->has('disabled') || (bool) $attributes->get('readonly')">
        @if ($icon)
            <x-crudPackage::ui.icon :name="$icon" :size="17" class="shrink-0 text-disabled" />
        @endif

        <input
            type="{{ $type }}"
            @if ($name) name="{{ $name }}" id="{{ $name }}" @endif
            value="{{ $value ?? ($name ? old($name) : null) }}"
            placeholder="{{ $placeholder ?? $label }}"
            @if ($required) required @endif
            {{ $attributes->class('flex-1 min-w-0 bg-transparent text-[15px] text-ink placeholder:text-faint outline-none disabled:text-muted read-only:text-muted read-only:cursor-not-allowed') }}
        >

        @if ($error)
            <span class="w-5 h-5 shrink-0 rounded-full border-2 border-danger text-danger flex items-center justify-center text-[11px] font-semibold">!</span>
        @endif

        @isset ($trailing)
            {{ $trailing }}
        @endisset
    </x-crudPackage::form.box>
</x-crudPackage::form.field>
