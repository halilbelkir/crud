@props([
    'name'        => null,
    'label'       => null,
    'value'       => null,
    'rows'        => 4,
    'required'    => false,
    'hint'        => null,
    'placeholder' => null,
])

@php
    $error = $name ? $errors->first(crudFieldKey($name)) : null;
@endphp

<x-crudPackage::form.field :label="$label" :name="$name" :required="$required" :hint="$hint">
    <x-crudPackage::form.box size="area" :error="$error" :disabled="$attributes->has('disabled')" multi>
        <textarea
            @if ($name) name="{{ $name }}" id="{{ $name }}" @endif
            rows="{{ $rows }}"
            placeholder="{{ $placeholder ?? $label }}"
            @if ($required) required @endif
            {{ $attributes->class('flex-1 min-w-0 bg-transparent text-[15px] text-ink placeholder:text-faint outline-none resize-y') }}
        >{{ $value ?? ($name ? old($name) : null) }}</textarea>
    </x-crudPackage::form.box>
</x-crudPackage::form.field>
