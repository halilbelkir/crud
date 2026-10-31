@props([
    'filters'  => [],
    'perPage'  => false,
    'compact'  => false,
])

@foreach ($filters as $filter)
    @php
        $filterName  = $filter['name'];
        $isContext   = (bool) ($filter['context'] ?? false);
        $isMultiple  = (bool) ($filter['multiple'] ?? false);
        $raw         = request($filterName) ?? ($filter['current'] ?? '');
        $activeValue = $isMultiple
            ? array_values(array_filter(explode(',', (string) $raw), 'strlen'))
            : (string) $raw;

        $items = [];

        $options = ($filter['sort'] ?? true) ? \crudPackage\Support\Sort::options($filter['options'] ?? []) : ($filter['options'] ?? []);

        foreach ($options as $value => $label)
        {
            $items[] = ['value' => (string) $value, 'label' => (string) $label];
        }

        $withText = $filter['searchable'] ?? count($items) > 10;
    @endphp

    @if (($filter['type'] ?? null) === 'date')
        <label
            x-data="{ value: @js($activeValue) }"
            data-select="filter-{{ $filterName }}"
            :class="value ? 'border-primary bg-tint-primary text-primary' : 'border-line-2 text-ink'"
            @class([
                'rounded-[20px] flex items-center border whitespace-nowrap cursor-pointer',
                'h-8 gap-1.5 px-3 text-[12.5px]' => $compact,
                'h-ctl gap-2.5 px-4 text-sm' => ! $compact,
            ])
        >
            {{ $filter['label'] }}

            <input
                type="date"
                x-model="value"
                @change="window.applyParams({ '{{ $filterName }}': $el.value })"
                class="bg-transparent outline-none cursor-pointer text-inherit"
            >
        </label>

        @continue
    @endif

    <div
        x-data="searchSelect({
            items: @js($items),
            value: @js($activeValue),
            placeholder: @js($filter['label']),
            searchable: @js($withText),
            multiple: @js($isMultiple),
            width: 240,
        })"
        data-select="filter-{{ $filterName }}"
        @change="window.applyParams({ '{{ $filterName }}': $event.target.value })"
        @click.away="hide()"
        @keydown.escape.stop="hide()"
        class="relative flex-none"
    >
        <input type="hidden" x-ref="input" :value="multiple ? value.join(',') : value">

        <button
            type="button"
            @click="toggle()"
            @class([
                'rounded-[20px] flex items-center border whitespace-nowrap',
                'h-8 gap-1.5 px-3 text-[12.5px]' => $compact,
                'h-ctl gap-2.5 px-4 text-sm' => ! $compact,
            ])
            :class="! empty && @js(! $isContext) ? 'border-primary bg-tint-primary text-primary' : 'border-line-2 text-ink'"
        >
            <span
                x-text="label ? @js($filter['label'] . ': ') + label : @js($filter['label'])"
                :title="label ? @js($filter['label'] . ': ') + label : @js($filter['label'])"
                @class(['truncate max-w-[130px] 2xl:max-w-none' => ! $compact])
            ></span>

            <x-crudPackage::ui.icon name="chevron-down" :size="$compact ? 12 : 14" class="shrink-0 text-muted transition-transform" ::class="open && 'rotate-180'" />
        </button>

        <div
            x-show="open"
            x-cloak
            x-ref="panel"
            :style="anchored"
            class="z-50 bg-card border border-line-2 rounded-menu shadow-menu overflow-hidden"
        >
            <x-crudPackage::ui.select-panel :with-text="$withText" :empty-label="$isContext ? null : 'Tümü'" />
        </div>
    </div>
@endforeach

@if ($perPage)
    <x-crudPackage::ui.dropdown align="left" width="w-[180px]" class="flex-none" data-refresh="per-page">
        <x-slot:trigger>
            <button type="button" @class([
                'rounded-[20px] border border-line-2 flex items-center text-ink hover:border-line-3 whitespace-nowrap',
                'h-8 gap-1.5 px-3 text-[12.5px]' => $compact,
                'h-ctl gap-2.5 px-4 text-sm' => ! $compact,
            ])>
                Sayfada: {{ \crudPackage\Support\Table::perPage() }}
                <x-crudPackage::ui.icon name="chevron-down" :size="$compact ? 12 : 14" class="text-muted" />
            </button>
        </x-slot:trigger>

        @foreach (\crudPackage\Support\Table::PER_PAGE_OPTIONS as $option)
            <x-crudPackage::ui.dropdown-item
                @click="open = false; window.applyParams({ sayfada: {{ \Illuminate\Support\Js::from($option) }} })"
                :active="\crudPackage\Support\Table::perPage() === $option"
            >
                <span class="w-2 h-2 rounded-full shrink-0 {{ \crudPackage\Support\Table::perPage() === $option ? 'bg-primary' : 'bg-line-3' }}"></span>
                {{ $option }} kayıt
            </x-crudPackage::ui.dropdown-item>
        @endforeach
    </x-crudPackage::ui.dropdown>
@endif
