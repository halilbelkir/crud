@php
    $inputValue      = null;
    $multiple        = null;
    $onKeyUpFunction = null;
    $name            = $column->column_name;
    $elementName     = !empty($language) ? $language->code.'['.$name.']' : $name;
    $elementId       = !empty($language) ? ($column->repeater == 1 ? 'repeater_'.$name.'_'.$language->code : $name.'_'.$language->code) : ($column->repeater == 1 ? 'repeater_'.$name : $name);

    if (isset($value) && $column->repeater == 1)
    {
        $inputValue = $value->{$column->column_name} ?? null;
    }
    else if (isset($column->detail))
    {
        $details  = json_decode($column->detail,true);

        if ($type == 'datetime')
        {
            $type = 'datetime-local';
        }
        else if ($type == 'image')
        {
            $type     = 'file';
            $multiple = isset($details['multiple']) && $details['multiple'] == true ? 'multiple' : null;
            $name     = $multiple == 'multiple' ? $name . '[]' : $name;
        }

        if (isset($details['slug-generate']))
        {
            $slugGenerateName = !empty($language) ? $language->code.'['.$details['slug-generate']['column_name'].']' : $details['slug-generate']['column_name'];
            $onKeyUpFunction  = "slugify(this.value,'[name=\"".$slugGenerateName."\"]')";
        }

        if (isset($value))
        {
            if ($formType->key == 'image')
            {
                $inputValue = Storage::disk('upload')->url($value->{$column->column_name});
            }
            else
            {
                $inputValue = $value->{$column->column_name};
            }
        }
        else
        {
            if ($type == 'date' || $type == 'datetime-local')
            {
                $inputValue = date('Y-m-d');
            }
        }
    }

@endphp

@if($formType->key == 'image' && !empty($inputValue) && isset($value) && $multiple != 'multiple')
    <div class="image-input image-input-outline showImage">
        <label class="btn btn-icon btn-circle btn-active-color-primary w-25px h-25px bg-body shadow" style="top: 0;left: 50%;" data-kt-image-input-action="change" data-bs-toggle="tooltip" data-bs-trigger="hover" title="Resmi Değiştir">
            <i class="ki-outline ki-pencil fs-7"></i>
            <input type="file" class="imageUpdate" value="{{ $inputValue }}" name="{{$elementName}}">
        </label>
        <img src="{{ empty($inputValue) ? asset('crud/images/no-image.png') : $inputValue }}"  class="mb-7 imageUpdatePreview w-100 object-fit-contain h-175px">
        <div id="preview"></div>
    </div>
@else
    <input
            type="{{$type}}"
            value="{{ $inputValue }}"
            name="{{ $multiple == 'multiple' ? $elementName.'[]' : $elementName }}"
            id="{{ $elementId }}"
            class="form-control form-control-solid mb-3 mb-lg-0"
            placeholder="{{$column->title}}"
            @if(isset($onKeyUpFunction))
                onkeyup="{{$onKeyUpFunction}}"
            @endif
            @if(isset($dt))
                data-route="{{route($crud->slug. '.realtime',$value->id)}}"
            onkeyup="crudRealtime(this)"
            @endif
            @if(isset($details['maxlength'])) maxlength="{{ $details['maxlength'] }}" @endif
            @if($column->required == 1 && $formType->key != 'image' && $languageKey == 0) required @endif
            {{$multiple}}
    >

    @if($formType->key == 'image' && isset($inputValue) && isset($value) && $multiple == 'multiple')
        <div class="row g-10 row-cols-2 row-cols-lg-5 mt-5">
            @if($copy == 1)
                <input type="hidden" value="{{ $inputValue }}" name="{{ $multiple == 'multiple' ? $elementName.'_copy[]' : $elementName.'_copy' }}" disabled>
            @endif

            @foreach(json_decode($inputValue) as $order => $image)
                @php $image = Storage::disk('upload')->url($image); @endphp
                <div class="col multipleImage">
                    <a class="d-block overlay" data-fslightbox="lightbox-hot-sales" href="{{$image}}">
                        <div class="overlay-wrapper bgi-no-repeat bgi-position-center bgi-size-cover card-rounded h-175px"
                             style="background-image:url({{$image}}">
                        </div>
                        <div class="overlay-layer card-rounded bg-dark bg-opacity-25">
                            <i class="ki-outline ki-eye fs-3x text-white"></i>
                        </div>
                    </a>

                    <div class="text-center mt-2">
                        <a data-route="{{route($crud->slug.'.fileDestroy',['id' => $value->id,'order' => $order,'column_name' => $column->column_name,'language_code' => $language->code ?? null,'language_order' => $languageKey ?? null])}}" onclick="destroy(this,{{$copy}})" data-title="{{($order+1).'. sıradaki resmi'}}" data-order="{{$order}}" class="btn btn-sm btn-light-danger" data-bs-toggle="tooltip" data-bs-trigger="hover" title="{{($order+1).'. sıradaki resmi sil'}}">
                            <i class="ki-outline ki-trash-square fs-2x"></i>
                        </a>
                    </div>
                </div>
            @endforeach
        </div>
    @endif
@endif