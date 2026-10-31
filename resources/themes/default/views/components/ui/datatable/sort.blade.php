@props([
    'columns' => [],
])

@php
    $options   = \crudPackage\Support\Table::sortOptions($columns);
    $field     = \crudPackage\Support\Table::sortColumn($columns);
    $direction = \crudPackage\Support\Table::sortDirection();
@endphp

@if ($options)
    <x-crudPackage::ui.dropdown width="w-[240px]" class="flex-none" data-refresh="sort">
        <x-slot:trigger>
            <button type="button" @class([
                'w-ctl h-ctl rounded-[20px] border flex items-center justify-center',
                'border-primary bg-tint-primary text-primary' => $field !== null,
                'border-line-2 text-body' => $field === null,
            ])>
                <x-crudPackage::ui.icon name="sort" :size="16" />
            </button>
        </x-slot:trigger>

        <span class="px-3 pt-1.5 pb-2 label-caps-sm">Sıralama</span>

        @foreach ($options as $option => $label)
            @php $active = $field === $option; @endphp

            <x-crudPackage::ui.dropdown-item
                @click="open = false; window.applyParams({ sirala: {{ \Illuminate\Support\Js::from($option) }}, yon: {{ \Illuminate\Support\Js::from($active && $direction === 'asc' ? 'desc' : 'asc') }} })"
                :active="$active"
            >
                <span class="w-2 h-2 rounded-full shrink-0 {{ $active ? 'bg-primary' : 'bg-line-3' }}"></span>

                {{ $label }}

                @if ($active)
                    <x-crudPackage::ui.icon :name="$direction === 'asc' ? 'chevron-up' : 'chevron-down'" :size="14" class="ml-auto shrink-0 text-primary" />
                @endif
            </x-crudPackage::ui.dropdown-item>
        @endforeach

        @if ($field !== null)
            <x-crudPackage::ui.dropdown-separator />

            <x-crudPackage::ui.dropdown-item @click="open = false; window.applyParams({ sirala: null, yon: null })">
                <span class="w-2 h-2 rounded-full shrink-0 bg-line-3"></span>
                Sıralamayı kaldır
            </x-crudPackage::ui.dropdown-item>
        @endif
    </x-crudPackage::ui.dropdown>
@endif
