@php
    $options = '';
    $details = json_decode($column->detail,true);

    if($column->relationship == 1)
    {
        $model            = $details['model'];
        $data             = $model::get();
        $showColumn       = $details['show_column'];
        $matchColumn      = $details['match_column'];

        foreach ($data as $option)
        {
            $options .= '<option value="'. $option->$matchColumn .'" '.( isset($value) && $option->$matchColumn == $value->{$column->column_name} ? 'selected' : null ).'> '. $option->$showColumn .' </option>';
        }
    }
    else
    {
        foreach ($details['items'] as $item)
        {
            $options .= '<option value="'. $item .'" '.( isset($value) && $item == $value->{$column->column_name} ? 'selected' : null ).'> '. $item .' </option>';
        }
    }
@endphp

<select name="{{$column->column_name}}"
        @if($type == 'select2') data-control="select2" data-placeholder="{{$column->title}} Seçiniz" data-allow-clear="true" @endif
        class="form-control form-control-solid"
        @if($column->required == 1) required @endif
        @if(isset($dt))
            data-route="{{route($crud->slug. '.realtime',$value->id)}}"
            onclick="crudRealtime(this)"
        @endif
>

    <option value="">{{$column->title}} Seçiniz</option>
    {!! $options !!}

</select>