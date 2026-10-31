@props([
    'name'    => null,
    'label'   => null,
    'value'   => 1,
    'checked' => false,
    'hint'    => null,
    'size'     => 'md',
    'wrapper'  => '',
    'required' => false,
])

@php
    $compact = $size === 'sm';
@endphp

<label @class([
    'relative flex cursor-pointer select-none',
    'items-center gap-2.5' => $compact,
    'items-start gap-3' => ! $compact,
    $wrapper => $wrapper !== '',
])>
    <input
        type="checkbox"
        @if ($name) name="{{ $name }}" id="{{ $name }}" @endif
        value="{{ $value }}"
        @checked($checked)
        @if ($required) required @endif
        {{ $attributes->class('peer absolute inset-0 m-0 w-full h-full opacity-0 appearance-none cursor-pointer') }}
    >

    <span @class([
        'shrink-0 border-2 border-line-3 flex items-center justify-center text-white transition-colors peer-checked:bg-primary peer-checked:border-primary peer-checked:[&>svg]:opacity-100 peer-disabled:bg-neutral peer-disabled:border-line-2',
        'w-[18px] h-[18px] rounded-md' => $compact,
        'w-[22px] h-[22px] mt-0.5 rounded-lg' => ! $compact,
    ])>
        <x-crudPackage::ui.icon name="check" :size="$compact ? 11 : 12" :stroke="3" class="opacity-0" />
    </span>

    <span class="min-w-0">
        @if ($label)
            <span class="block text-sm text-ink">{{ $label }}</span>
        @endif

        @if ($hint)
            <span class="block text-xs text-muted mt-0.5">{{ $hint }}</span>
        @endif

        {{ $slot }}
    </span>
</label>
