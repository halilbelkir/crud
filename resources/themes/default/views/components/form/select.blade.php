@props([
    'name'      => null,
    'label'     => null,
    'options'   => [],
    'selected'  => null,
    'size'      => 'md',
    'required'  => false,
    'hint'      => null,
    'placeholder' => null,
    'searchable'  => null,
    'sort'        => true,
    'multiple'    => false,
    'icons'       => false,
])

@php
    $error   = $name ? $errors->first(crudFieldKey($name)) : null;
    $current = $selected ?? ($name ? old($name) : null);

    $items = [];

    foreach ($sort ? \crudPackage\Support\Sort::options($options) : $options as $key => $option)
    {
        $items[] = [
            'value' => (string) $key,
            'label' => (string) $option,
            'icon'  => $icons ? \Illuminate\Support\Facades\Blade::render('<x-crudPackage::ui.icon :name="$icon" :size="17" />', ['icon' => (string) $key]) : null,
        ];
    }

    $prompt   = $placeholder ?? ($label ? $label . ' seçin' : 'Seçin');
    $withText = $searchable ?? count($items) > 10;
    $locked   = $attributes->has('disabled') || $attributes->has('readonly');
@endphp

<x-crudPackage::form.field :label="$label" :name="$name" :required="$required" :hint="$hint">
    <div
        x-data="searchSelect({
            items: @js($items),
            value: @js($multiple ? array_map('strval', (array) $current) : (string) $current),
            multiple: @js((bool) $multiple),
            placeholder: @js($prompt),
            searchable: @js($withText),
            locked: @js($locked),
        })"
        @if ($name) data-select="{{ $name }}" @endif
        x-on:options-set="load($event.detail)"
        x-on:option-pick="commit($event.detail)"
        @keydown.escape.stop="hide()"
        @click.away="hide()"
        {{ $attributes->class('relative') }}
    >
        @if ($multiple)
            @if ($name)
                <input type="hidden" name="{{ $name }}" value="" :disabled="value.length > 0">
            @endif

            <template x-for="item in value" :key="item">
                <input type="hidden" @if ($name) name="{{ $name }}[]" @endif :value="item">
            </template>
        @else
            <input type="hidden" x-ref="input" @if ($name) name="{{ $name }}" @endif :value="value">
        @endif

        <x-crudPackage::form.box
            :size="$size"
            :error="$error"
            :disabled="$attributes->has('disabled')"
            ::class="[locked ? '' : 'cursor-pointer', open && '!border-primary']"
            @click="toggle()"
        >
            @if ($icons && ! $multiple)
                <span x-show="current?.icon" class="shrink-0 flex text-ink" x-html="current?.icon"></span>
            @endif

            <span
                @if ($name) id="{{ $name }}" @endif
                class="flex-1 min-w-0 truncate text-[15px]"
                :class="label ? 'text-ink' : 'text-faint'"
                x-text="label || placeholder"
            ></span>

            <x-crudPackage::ui.icon name="chevron-down" :size="16" class="shrink-0 text-muted transition-transform" ::class="open && 'rotate-180 text-primary'" x-show="! locked" />
        </x-crudPackage::form.box>

        <div
            x-show="open"
            x-cloak
            x-ref="panel"
            :style="anchored"
            class="z-50 bg-card border border-line-2 rounded-menu shadow-menu overflow-hidden"
        >
            <x-crudPackage::ui.select-panel :with-text="$withText" :empty-label="$required ? null : $prompt" />
        </div>
    </div>
</x-crudPackage::form.field>
