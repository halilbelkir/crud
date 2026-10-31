@props([
    'search',
    'compact' => false,
])

@php
    $name = $search['name'] ?? 'q';
@endphp

<form
    method="GET"
    x-data
    @submit.prevent="window.filterContent(@js($name), $refs.term.value)"
    @class(['flex-1 min-w-0' => $compact, 'flex-1 min-w-[180px] max-w-[240px]' => ! $compact])
>
    @foreach (request()->except([$name, 'page']) as $key => $value)
        <input type="hidden" name="{{ $key }}" value="{{ $value }}">
    @endforeach

    <label @class([
        'h-ctl rounded-[20px] border border-line-2 flex items-center gap-2.5 focus-within:border-line-3',
        'px-3.5' => $compact,
        'w-full px-4' => ! $compact,
    ])>
        <x-crudPackage::ui.icon name="search" :size="15" class="text-disabled shrink-0" />

        <input
            type="search"
            x-ref="term"
            name="{{ $name }}"
            value="{{ request($name) }}"
            placeholder="{{ $search['placeholder'] ?? 'Ara' }}"
            @input.debounce.400ms="window.filterContent(@js($name), $el.value)"
            @search="window.filterContent(@js($name), $el.value)"
            @class([
                'flex-1 min-w-0 bg-transparent text-ink placeholder:text-faint outline-none',
                'text-[13.5px]' => $compact,
                'text-sm' => ! $compact,
            ])
        >
    </label>
</form>
