@props([
    'name'     => null,
    'label'    => null,
    'value'    => 1,
    'checked'  => false,
    'hint'     => null,
    'onLabel'  => null,
    'offLabel' => null,
])

<label
    x-data="{ on: {{ $checked ? 'true' : 'false' }} }"
    {{ $attributes->class('flex items-center gap-3 cursor-pointer select-none') }}
>
    @if ($name)
        <input type="hidden" name="{{ $name }}" value="0">
    @endif

    <input
        type="checkbox"
        @if ($name) name="{{ $name }}" id="{{ $name }}" @endif
        value="{{ $value }}"
        @checked($checked)
        x-model="on"
        class="peer sr-only"
    >

    <span class="w-[46px] h-6 shrink-0 rounded-full bg-line-3 relative transition-colors peer-checked:bg-primary peer-checked:[&>span]:translate-x-[22px]">
        <span class="absolute top-0.5 left-0.5 w-5 h-5 rounded-full bg-card transition-transform"></span>
    </span>

    <span class="min-w-0">
        @if ($onLabel && $offLabel)
            <span class="block text-sm text-ink" x-text="on ? @js($onLabel) : @js($offLabel)"></span>
        @elseif ($label)
            <span class="block text-sm text-ink">{{ $label }}</span>
        @endif

        @if ($hint)
            <span class="block text-xs text-muted mt-0.5">{{ $hint }}</span>
        @endif
    </span>
</label>
