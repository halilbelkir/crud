@php
    $name        = $column->column_name;
    $elementName = !empty($language) ? $language->code.'['.$name.']' : $name;
    $elementId   = !empty($language) ? ($column->repeater == 1 ? 'repeater_'.$name.'_'.$language->code : $name.'_'.$language->code) : ($column->repeater == 1 ? 'repeater_'.$name : $name);
    $detail      = isset($column->detail) ? json_decode($column->detail) : null;
@endphp
<textarea
        name="{{ $elementName }}"
        cols="30"
        @if($type == 'editor') data-editor="true" @endif
        @if(isset($detail->rows)) rows="{{ $detail->rows }}" @else rows="5" @endif
        @if(isset($detail->maxlength)) maxlength="{{ $detail->maxlength }}" @endif
        class="form-control form-control-solid mb-3 mb-lg-0"
        placeholder="{{$column->title}}"
        id="{{ $elementId }}"
        @if($type != 'editor' && $column->required == 1 && $languageKey == 0) required @endif
>{!! isset($value) ? $value->{$column->column_name} : null !!}</textarea>