@php
    $inputValue      = null;
    $multiple        = null;
    $onKeyUpFunction = null;
    $name            = $column->column_name;

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
            $onKeyUpFunction = "slugify(this.value,'[name=\"".$details['slug-generate']['column_name']."\"]')";
        }

        if (isset($value))
        {
            $inputValue = $value->{$column->column_name};
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

@if($formType->key == 'image' && isset($inputValue) && isset($value) && $multiple != 'multiple')
    <div class="image-input image-input-outline showImage">
        <label class="btn btn-icon btn-circle btn-active-color-primary w-25px h-25px bg-body shadow" style="top: 0;left: 50%;" data-kt-image-input-action="change" data-bs-toggle="tooltip" data-bs-trigger="hover" title="Resmi Değiştir">
            <i class="ki-outline ki-pencil fs-7"></i>
            <input type="file" class="imageUpdate" value="{{ $inputValue }}" name="{{$name}}">
        </label>
        <img src="{{$inputValue}}"  class="mb-7 imageUpdatePreview w-100 object-fit-contain h-175px">
        <div id="preview"></div>
    </div>
@else
    <input
            type="{{$type}}"
            value="{{ $inputValue }}"
            name="{{$name}}"
            id="{{$column->repeater == 1 ? 'repeater_'.$name : $name}}"
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
            @if($column->required == 1 && $formType->key != 'image') required @endif
            {{$multiple}}
    >

    @if($formType->key == 'image' && isset($inputValue) && isset($value) && $multiple == 'multiple')
        <div class="row g-10 row-cols-2 row-cols-lg-5 mt-5">
            @foreach(json_decode($inputValue) as $order => $image)
                <div class="col">
                    <a class="d-block overlay" data-fslightbox="lightbox-hot-sales" href="{{$image}}">
                        <div class="overlay-wrapper bgi-no-repeat bgi-position-center bgi-size-cover card-rounded h-175px"
                             style="background-image:url({{$image}}">
                        </div>
                        <div class="overlay-layer card-rounded bg-dark bg-opacity-25">
                            <i class="ki-outline ki-eye fs-3x text-white"></i>
                        </div>
                    </a>

                    <div class="text-center mt-2">
                        <a data-route="{{route($crud->slug.'.fileDestroy',['id' => $value->id,'order' => $order,'column_name' => $column->column_name])}}" onclick="destroy(this)" data-title="{{($order+1).'. sıradaki resmi'}}" class="btn btn-sm btn-light-danger" data-bs-toggle="tooltip" data-bs-trigger="hover" title="{{($order+1).'. sıradaki resmi sil'}}">
                            <i class="ki-outline ki-trash-square fs-2x"></i>
                        </a>
                    </div>
                </div>
            @endforeach
        </div>
    @endif
@endif