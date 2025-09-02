@php
    $details = json_decode($column->detail,true);
    $values  = isset($value) ? json_decode($value->{$column->column_name}) : null;
@endphp

@if($type == 'switch')
    <label class="form-check crud-switch form-switch form-check-custom form-check-solid flex-stack d-block">
        <input
                class="form-check-input"
                name="{{$column->column_name}}"
                type="checkbox"
                data-on="{{$details['on']}}"
                data-off="{{$details['off']}}"
                value="1"
                @if(isset($dt))
                    data-route="{{route($crud->slug. '.realtime',$value->id)}}"
                    onclick="crudRealtime(this)"
                @endif
                @if($column->required == 1) required @endif
                @if(isset($value) && $value->{$column->column_name} == 1 || empty($value) && $details['checked']) checked @endif
        />
    </label>
@else
    <div class="form-check form-check-sm mb-3">
        <input class="form-check-input" name="{{$column->column_name}}" onchange="allCheck(this)" id="{{$column->column_name}}_{{$column->id}}_all" data-checkbox-class="{{$column->column_name}}_{{$column->id}}" type="checkbox" />
        <label class="form-check-label text-gray-700 fw-semibold" for="{{$column->column_name}}_{{$column->id}}_all">
            Hepsini Seç
        </label>
    </div>
    @foreach ($details['items'] as $key => $item)
        <div class="form-check crud-checkbox form-check-sm mb-3">
            <input class="form-check-input {{$column->column_name}}_{{$column->id}}"
                   id="{{$column->column_name}}_{{$key}}"
                   name="{{$column->column_name}}[{{$key}}]"
                   type="checkbox"
                   value="{{$item}}"
                   @if(is_array($values) && in_array($item, $values)) checked @endif
            />
            <label class="form-check-label text-gray-700 fw-semibold" for="{{$column->column_name}}_{{$key}}">
                {{$item}}
            </label>
        </div>
    @endforeach
@endif
