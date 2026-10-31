@php
    use crudPackage\Library\Relationships\CrudRelationships;

    $details     = json_decode($column->detail ?? '{}', true) ?? [];
    $name        = $column->column_name;
    $elementName = isset($column->repeater_name) ? $column->repeater_name . '[' . $name . ']' : (! empty($language) ? $language->code . '[' . $name . ']' : $name);
    $multiple    = ! empty($details['multiple']);
    $required    = $column->required == 1 && empty($languageKey);
    $options     = [];
    $selected    = isset($value) ? ($value->{$name} ?? null) : null;
    $skip        = ($details['type'] ?? null) === 'belongsToMany' && ! empty($languageKey);
    $dependsOn   = $column->relationship == 1 ? ($details['depends_on'] ?? null) : null;
    $parentName  = $dependsOn ? (isset($column->repeater_name) ? $column->repeater_name . '[' . $dependsOn['field'] . ']' : (! empty($language) ? $language->code . '[' . $dependsOn['field'] . ']' : $dependsOn['field'])) : null;

    if (! $skip && $column->relationship == 1)
    {
        $model     = $details['model'];
        $query     = isset($details['scope']) ? $model::{$details['scope']}() : $model::query();

        if ($dependsOn)
        {
            $parentValue = isset($value) ? ($value->{$dependsOn['field']} ?? null) : null;
            $data        = filled($parentValue) ? $query->where($dependsOn['column'], $parentValue)->get() : collect();
        }
        else
        {
            $data = $query->get();
        }

        $options = $data->mapWithKeys(fn ($option) => [$option->{$details['match_column']} => $option->{$details['show_column']}])->all();

        if ($multiple && ($details['type'] ?? null) === 'belongsToMany' && isset($originalValue))
        {
            $relationship = CrudRelationships::generateName($crud->model, $name);
            $selected     = $originalValue->{$relationship}?->pluck($details['match_column'])->all() ?? [];
        }
        elseif ($multiple)
        {
            $selected = json_decode((string) $selected, true) ?? [];
        }
    }
    elseif (! $skip)
    {
        $options = $details['items'] ?? [];

        if ($multiple)
        {
            $selected = is_array($selected) ? $selected : (json_decode((string) $selected, true) ?? []);
        }
    }

    if (! isset($column->repeater_name))
    {
        $selected = old(crudFieldKey($elementName), $selected);
    }
@endphp

@unless ($skip)
    <x-crudPackage::form.select
        :name="$elementName"
        :options="$options"
        :selected="$selected"
        :multiple="$multiple"
        :placeholder="$column->title . ' seçin'"
        :required="$required"
        :searchable="$formType->key === 'select2' ? true : null"
        :sort="false"
        :size="$size ?? 'md'"
        :data-depends-field="$parentName"
        :data-options-url="$dependsOn ? route('single.relationOptions', ['crud' => $crud->id, 'column' => $name]) : null"
    />
@endunless
