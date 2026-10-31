@php
    $details     = json_decode($column->detail ?? '{}', true) ?? [];
    $name        = $column->column_name;
    $elementName = isset($column->repeater_name) ? $column->repeater_name . '[' . $name . ']' : (! empty($language) ? $language->code . '[' . $name . ']' : $name);
    $stored      = isset($value) ? ($value->{$name} ?? null) : null;
    $current     = isset($column->repeater_name) ? $stored : old(crudFieldKey($elementName), $stored);
    $required    = $column->required == 1 && empty($languageKey);
@endphp

@if ($formType->key === 'editor')
    <x-crudPackage::form.editor :name="$elementName" :value="$current" :height="$details['height'] ?? 320" />
@else
    <x-crudPackage::form.textarea
        :name="$elementName"
        :value="$current"
        :rows="$details['rows'] ?? 5"
        :placeholder="$column->title"
        :required="$required"
        :maxlength="$details['maxlength'] ?? null"
    />
@endif
