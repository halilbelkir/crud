@props([
    'align' => 'right',
    'width' => 'w-[252px]',
])

<div
    x-data="{ open: false, up: false }"
    @click.outside="open = false"
    @keydown.escape.window="open = false"
    {{ $attributes->class('relative') }}
>
    <div @click="up = $el.getBoundingClientRect().bottom > window.innerHeight - 280; open = ! open; if (open) { $nextTick(() => requestAnimationFrame(() => window.placeDropdown($root))) }">
        {{ $trigger }}
    </div>

    <div
        x-show="open"
        x-cloak
        x-transition.opacity.duration.150ms
        :class="up ? 'bottom-[48px]' : 'top-[48px]'"
        data-dropdown-panel
        @class([
            'absolute z-30 bg-card border border-line-2 rounded-menu shadow-menu p-2 flex flex-col gap-0.5 text-left',
            $width,
            'right-0' => $align === 'right',
            'left-0' => $align === 'left',
        ])
    >
        {{ $slot }}
    </div>
</div>
