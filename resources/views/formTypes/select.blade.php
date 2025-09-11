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

        foreach ($data as $option)
        {
            $options .= '<option value="'. $option->$matchColumn .'" '.( isset($value) && $option->$matchColumn == $value->{$name} ? 'selected' : null ).'> '. $option->$showColumn .' </option>';
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

<select name="{{$name}}"
        @if($type == 'select2') data-control="select2" data-placeholder="{{$column->title}} Seçiniz" data-allow-clear="true" @endif
        class="form-control form-control-solid"
        id="{{$column->repeater == 1 ? 'repeater_'.$name : $name}}"
        @if($column->required == 1) required @endif
        @if(isset($dt))
            data-route="{{route($crud->slug. '.realtime',$value->id)}}"
        onclick="crudRealtime(this)"
        @endif
>

    <option value="">{{$column->title}} Seçiniz</option>
    {!! $options !!}

</select>