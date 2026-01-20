@php
    $langTitle = isset($language) ? ' ('. $language->title.')' : null;
    $langCode  = isset($language) ? '_'.$language->code : null;
    $repeaterList  = isset($language) ? $language->code.'['. $column->column_name .']' : $column->column_name;
@endphp

<div id="{{$column->column_name}}_repeater{{$langCode}}" data-repeater-crud>
    <div class="separator separator-content border-dark my-15"><span class="w-250px h2">{{$column->title.$langTitle}}</span></div>
    <div data-repeater-list="{{$repeaterList}}">
        {!! $elements !!}
    </div>
    <div class="form-group mt-5">
        <a href="javascript:;" data-repeater-create class="btn btn-primary">
            <i class="ki-duotone ki-plus fs-3"></i>
            Satır Ekle
        </a>
    </div>
</div>