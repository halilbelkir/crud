@php
    $options = '';
    $details = json_decode($column->detail,true);
    $name    = $column->column_name;

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
        id="{{$column->repeater == 1 ? 'repeater_'.$name : $name}}"
        @if($column->required == 1) required @endif
        @if(isset($dt))
            data-route="{{route($crud->slug. '.realtime',$value->id)}}"
            onclick="crudRealtime(this)"
        @endif
        @if(isset($details['multiple']))
            data-close-on-select="false"
            data-hide-search="false"
            multiple="multiple"
            name="{{$name}}[]"
        @else
            name="{{$name}}"
        @endif
>

    <option value="">{{$column->title}} Seçiniz</option>
    {!! $options !!}

</select>