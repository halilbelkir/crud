@php
    $options     = '';
    $details     = json_decode($column->detail,true);
    $name        = $column->column_name;
    $elementName = !empty($language) ? $language->code.'['.$name.']' : $name;
    $elementId   = !empty($language) ? ($column->repeater == 1 ? 'repeater_'.$name.'_'.$language->code : $name.'_'.$language->code) : ($column->repeater == 1 ? 'repeater_'.$name : $name);

    if($column->relationship == 1)
    {
        $model = $details['model'];

        if (isset($details['scope']))
        {
            $scope = $details['scope'];
            $data  = $model::{$scope}()->get();
        }
        else
        {
            $data = $model::get();
        }

        $showColumn  = $details['show_column'];
        $matchColumn = $details['match_column'];
        $selected    = [];

        foreach ($data as $option)
        {
            if (isset($details['multiple']) && isset($value->{$name}))
            {
                $values = json_decode($value->{$name});

                foreach ($values as $newValue)
                {
                    $selected[$newValue] = $option->$matchColumn == $newValue ? true : false;
                }

                $options .= '<option value="'. $option->$matchColumn .'" '.( isset($value) && isset($selected[$option->$matchColumn]) &&  $selected[$option->$matchColumn] == true ? 'selected' : null ).'> '. $option->$showColumn .' </option>';
            }
            else
            {
                $options .= '<option value="'. $option->$matchColumn .'" '.( isset($value) && $option->$matchColumn == $value->{$name} ? 'selected' : null ).'> '. $option->$showColumn .' </option>';
            }
        }
    }
    else
    {
        foreach ($details['items'] as $key => $item)
        {
            $options .= '<option value="'. $key .'" '.( isset($value) && $key == $value->{$name} ? 'selected' : null ).'> '. $item .' </option>';
        }
    }
@endphp

<select
        @if($type == 'select2') data-control="select2"  data-placeholder="{{$column->title}} Seçiniz" data-allow-clear="true" @endif
        class="form-control form-control-solid"
        id="{{ $elementId }}"
        @if($column->required == 1 && $languageKey == 0) required @endif
        @if(isset($dt))
            data-route="{{route($crud->slug. '.realtime',$value->id)}}"
            onclick="crudRealtime(this)"
        @endif
        @if(isset($details['multiple']))
            data-select-multiple="true"
            multiple="multiple"
            name="{{$elementName}}[]"
        @else
            name="{{$elementName}}"
        @endif
>

    <option value="">{{$column->title}} Seçiniz</option>
    {!! $options !!}

</select>