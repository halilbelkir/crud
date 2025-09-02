<textarea
        name="{{$column->column_name}}"
        cols="30"
        @if($type == 'editor') data-editor="true" @endif
        rows="5"
        class="form-control form-control-solid mb-3 mb-lg-0"
        placeholder="{{$column->title}}"
        @if($type != 'editor' && $column->required == 1) required @endif
>{!! isset($value) ? $value->{$column->column_name} : null !!}</textarea>