@php
    $details     = json_decode($column->detail ?? '{}', true) ?? [];
    $name        = $column->column_name;
    $elementName = isset($column->repeater_name) ? $column->repeater_name . '[' . $name . ']' : (! empty($language) ? $language->code . '[' . $name . ']' : $name);
    $stored      = isset($value) ? ($value->{$name} ?? null) : null;
    $current     = isset($column->repeater_name) ? $stored : old(crudFieldKey($elementName), $stored);
@endphp

<x-crudPackage::form.segmented
    :name="$elementName"
    :options="$details['items'] ?? []"
    :selected="$current"
/>
