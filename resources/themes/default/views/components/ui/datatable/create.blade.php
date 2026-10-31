@props([
    'create',
    'compact' => false,
])

@php
    $label   = $create['label'] ?? 'Yeni kayıt';
    $short   = $create['short'] ?? 'Yeni';
    $shorten = $compact || isset($create['short']);

    $text = match (true)
    {
        $compact => e($short),
        $shorten => '<span class="2xl:hidden">' . e($short) . '</span><span class="hidden 2xl:inline">' . e($label) . '</span>',
        default  => e($label),
    };
@endphp

@if ($modal = $create['modal'] ?? null)
    <x-crudPackage::ui.button
        x-data
        @click="$dispatch('modal-open', {{ \Illuminate\Support\Js::from($modal) }})"
        :variant="$create['tone'] ?? 'accent'"
        :size="$compact ? 'xs' : 'sm'"
        :trailing="$create['icon'] ?? ($compact ? 'plus-plain' : 'plus')"
        :title="$shorten ? $label : null"
    >
        {!! $text !!}
    </x-crudPackage::ui.button>
@elseif ($create['drawer'] ?? true)
    <x-crudPackage::ui.button
        x-data
        @click="$dispatch('form-drawer', { url: {{ \Illuminate\Support\Js::from($create['href']) }}, title: {{ \Illuminate\Support\Js::from($label) }} })"
        :variant="$create['tone'] ?? 'accent'"
        :size="$compact ? 'xs' : 'sm'"
        :trailing="$create['icon'] ?? ($compact ? 'plus-plain' : 'plus')"
        :title="$shorten ? $label : null"
    >
        {!! $text !!}
    </x-crudPackage::ui.button>
@else
    <x-crudPackage::ui.button
        :href="$create['href']"
        :variant="$create['tone'] ?? 'accent'"
        :size="$compact ? 'xs' : 'sm'"
        :trailing="$create['icon'] ?? ($compact ? 'plus-plain' : 'plus')"
        :title="$shorten ? $label : null"
    >
        {!! $text !!}
    </x-crudPackage::ui.button>
@endif
