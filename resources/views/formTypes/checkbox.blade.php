@php
    $details     = json_decode($column->detail,true);
    $values      = isset($value) ? json_decode($value->{$column->column_name}) : null;
    $name        = $column->column_name;
    $elementName = !empty($language) ? $language->code.'['.$name.']' : $name;
    $elementId   = !empty($language) ? ($column->repeater == 1 ? 'repeater_'.$name.'_'.$language->code : $name.'_'.$language->code) : ($column->repeater == 1 ? 'repeater_'.$name : $name);
@endphp

@if($type == 'switch')
    <label class="form-check crud-switch form-switch form-check-custom form-check-solid flex-stack d-block">
        <input
                class="form-check-input"
                name="{{ $elementName }}"
                type="checkbox"
                data-on="{{$details['on']}}"
                data-off="{{$details['off']}}"
                value="1"
                @if(isset($dt))
                    data-route="{{route($crud->slug. '.realtime',$value->id)}}"
                    onclick="crudRealtime(this)"
                @endif
                @if($column->required == 1 && $languageKey == 0) required @endif
                @if(isset($value) && $value->{$column->column_name} == 1 || empty($value) && $details['checked']) checked @endif
        />
    </label>
@else
    <div class="form-check form-check-sm mb-3">
        <input class="form-check-input" name="{{ $elementName }}" onchange="allCheck(this)" id="{{$elementId}}_{{$column->id}}_all" data-checkbox-class="{{$elementId}}_{{$column->id}}" type="checkbox" />
        <label class="form-check-label text-gray-700 fw-semibold" for="{{$elementId}}_{{$column->id}}_all">
            Hepsini Seç
        </label>
    </div>
    @foreach ($details['items'] as $key => $item)
        <div class="form-check crud-checkbox form-check-sm mb-3">
            <input class="form-check-input {{$elementId}}_{{$column->id}}"
                   id="{{$elementId}}_{{$key}}"
                   name="{{ $elementName }}[{{$key}}]"
                   type="checkbox"
                   value="{{$item}}"
                   @if(is_array($values) && in_array($item, $values)) checked @endif
            />
            <label class="form-check-label text-gray-700 fw-semibold" for="{{$elementId}}_{{$key}}">
                {{$item}}
            </label>
        </div>
    @endforeach
@endif
