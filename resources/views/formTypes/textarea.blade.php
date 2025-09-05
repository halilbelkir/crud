@php $name = $column->column_name; @endphp
<textarea
        name="{{$name}}"
        cols="30"
        @if($type == 'editor') data-editor="true" @endif
        @if(isset($column->rows)) rows="{{ $column->rows }}" @else rows="5" @endif
        class="form-control form-control-solid mb-3 mb-lg-0"
        placeholder="{{$column->title}}"
        id="{{$column->repeater == 1 ? 'repeater_'.$name : $name}}"
        @if($type != 'editor' && $column->required == 1) required @endif
>{!! isset($value) ? $value->{$column->column_name} : null !!}</textarea>