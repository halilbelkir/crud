@php
        $name = $column->column_name;
        $detail = json_decode($column->detail);
@endphp
<textarea
        name="{{$name}}"
        cols="30"
        @if($type == 'editor') data-editor="true" @endif
        @if(isset($detail->rows)) rows="{{ $detail->rows }}" @else rows="5" @endif
        @if(isset($detail->maxlength)) maxlength="{{ $detail->maxlength }}" @endif
        class="form-control form-control-solid mb-3 mb-lg-0"
        placeholder="{{$column->title}}"
        id="{{$column->repeater == 1 ? 'repeater_'.$name : $name}}"
        @if($type != 'editor' && $column->required == 1) required @endif
>{!! isset($value) ? $value->{$column->column_name} : null !!}</textarea>