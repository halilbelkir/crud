@props([
    'label'    => null,
    'name'     => null,
    'required' => false,
    'hint'     => null,
])

@php
    $error = $name ? $errors->first(crudFieldKey($name)) : null;
@endphp

<div data-field data-form-cell {{ $attributes->class('flex flex-col gap-2') }}>
    @if ($label)
        <label @if ($name) for="{{ $name }}" @endif class="label-caps">
            {{ $label }}
            @if ($required)
                <span class="text-danger">*</span>
            @endif
        </label>
    @endif

    {{ $slot }}

    @if ($error)
        <span class="text-xs text-danger">{{ $error }}</span>
    @elseif ($hint)
        <span class="text-xs text-muted">{{ $hint }}</span>
    @endif
</div>
