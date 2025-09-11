<div id="{{$column->column_name}}" data-repeater-crud>
    <div class="separator separator-content border-dark my-15"><span class="w-250px h2">{{$column->title}}</span></div>
    <div data-repeater-list="{{$column->column_name}}">
        {!! $elements !!}
    </div>
    <div class="form-group mt-5">
        <a href="javascript:;" data-repeater-create class="btn btn-light-primary">
            <i class="ki-duotone ki-plus fs-3"></i>
            Satır Ekle
        </a>
    </div>
</div>