@php
    $details     = json_decode($column->detail ?? '{}', true) ?? [];
    $name        = $column->column_name;
    $elementName = isset($column->repeater_name) ? $column->repeater_name . '[' . $name . ']' : (! empty($language) ? $language->code . '[' . $name . ']' : $name);
    $stored      = isset($value) ? ($value->{$name} ?? null) : null;
    $current     = isset($column->repeater_name) ? $stored : old(crudFieldKey($elementName), $stored);
@endphp

@if ($formType->key === 'switch')
    <x-crudPackage::form.toggle
        :name="$elementName"
        :on-label="$details['on'] ?? 'Aktif'"
        :off-label="$details['off'] ?? 'Pasif'"
        :checked="isset($value) || old() ? (string) $current === '1' : ! empty($details['checked'])"
    />
@else
    @php
        $items  = array_values($details['items'] ?? []);
        $picked = is_array($current) ? $current : (json_decode((string) $current, true) ?? []);
    @endphp

    <div
        x-data="{ items: @js($items), picked: @js(array_values(array_intersect($items, $picked))) }"
        class="flex flex-col gap-3"
    >
        <input type="hidden" name="{{ $elementName }}" :value="JSON.stringify(picked)">

        <x-crudPackage::form.checkbox label="Hepsini seç" x-bind:checked="picked.length === items.length" x-on:change="picked = $event.target.checked ? [...items] : []" />

        <div class="grid @sm:grid-cols-2 @4xl:grid-cols-3 gap-3">
            @foreach ($items as $item)
                <x-crudPackage::form.checkbox
                    :label="$item"
                    :value="$item"
                    x-model="picked"
                />
            @endforeach
        </div>
    </div>
@endif
