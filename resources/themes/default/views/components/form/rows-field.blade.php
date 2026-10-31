@props([
    'group',
    'index',
    'field',
    'value' => null,
])

@php
    $name = $group . '[' . $index . '][' . $field['key'] . ']';
@endphp

@if (($field['type'] ?? 'input') === 'textarea')
    <x-crudPackage::form.textarea
        :name="$name"
        :value="$value"
        :rows="$field['rows'] ?? 2"
        :placeholder="$field['placeholder'] ?? null"
    />
@else
    <x-crudPackage::form.input
        :name="$name"
        :value="$value"
        :placeholder="$field['placeholder'] ?? null"
    />
@endif
