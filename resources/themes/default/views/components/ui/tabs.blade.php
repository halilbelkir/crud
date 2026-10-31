@props([
    'tabs'    => [],
    'current' => null,
])

@php
    $items = [];

    foreach ($tabs as $key => $tab)
    {
        $items[$key] = is_array($tab)
            ? $tab
            : ['label' => $tab];
    }

    $active = $current ?? array_key_first($items);
@endphp

<div x-data="{ tab: @js($active) }" {{ $attributes->class('flex flex-col min-h-0') }}>

    <div class="lg:hidden h-11 rounded-[22px] bg-card border border-line p-[3px] flex gap-[3px] shrink-0">
        @foreach ($items as $key => $item)
            <button
                type="button"
                @if (empty($item['disabled'])) @click="tab = @js($key)" @else disabled @endif
                class="flex-1 rounded-[19px] flex items-center justify-center gap-2 text-[13.5px] transition-colors disabled:text-disabled"
                :class="tab === @js($key) ? 'bg-primary text-white font-medium' : 'text-body'"
            >
                {{ $item['short'] ?? $item['label'] }}
            </button>
        @endforeach
    </div>

    <div class="hidden lg:flex items-center gap-7 px-6 border-b border-line-soft shrink-0">
        @foreach ($items as $key => $item)
            <button
                type="button"
                @if (empty($item['disabled'])) @click="tab = @js($key)" @else disabled @endif
                class="flex items-center gap-[9px] pt-[17px] pb-[14px] -mb-px text-[15px] border-b-[3px] transition-colors disabled:text-disabled"
                :class="tab === @js($key) ? 'text-primary font-medium border-primary' : 'text-muted border-transparent'"
            >
                @isset ($item['icon'])
                    <x-crudPackage::ui.icon :name="$item['icon']" :size="17" class="shrink-0" />
                @endisset

                {{ $item['label'] }}
            </button>
        @endforeach

        @isset ($meta)
            <div class="ml-auto flex items-center gap-2 text-[13px] text-muted">{{ $meta }}</div>
        @endisset
    </div>

    {{ $slot }}
</div>
