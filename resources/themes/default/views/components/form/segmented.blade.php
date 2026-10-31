@props([
    'name'     => null,
    'label'    => null,
    'options'  => [],
    'selected' => null,
    'required' => false,
    'hint'     => null,
])

@php
    $current = $selected ?? ($name ? old($name) : null) ?? (string) array_key_first($options);
@endphp

<x-crudPackage::form.field :label="$label" :name="$name" :required="$required" :hint="$hint">
    <div
        x-data="{ choice: @js((string) $current) }"
        {{ $attributes->class('h-[46px] rounded-[23px] lg:h-field-dense lg:rounded-[22px] border border-line-2 p-[3px] flex items-center gap-[3px]') }}
    >
        @foreach ($options as $value => $text)
            <label
                class="flex-1 h-full rounded-[19px] lg:rounded-[18px] flex items-center justify-center text-[13.5px] cursor-pointer transition-colors"
                :class="choice === @js((string) $value) ? 'bg-primary text-white font-medium' : 'text-body'"
            >
                <input
                    type="radio"
                    @if ($name) name="{{ $name }}" @endif
                    value="{{ $value }}"
                    x-model="choice"
                    class="sr-only"
                >

                {{ $text }}
            </label>
        @endforeach
    </div>
</x-crudPackage::form.field>
