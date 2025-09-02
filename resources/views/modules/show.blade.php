@extends('crudPackage::layout.main',['activePage' => $crud->display_single.' Detay','parentPage' => $crud->display_plural,'parentPageRoute' => route($crud->slug .'.index')])
@section('content')
    @foreach($crud->readColumns as $column)
        @php
            $formType   = $column->type;
            $inputValue = $values->{$column->column_name};
            $details    = json_decode($column->detail,true);
            $multiple   = isset($details['multiple']) && $details['multiple'] == true ? 'multiple' : null;
        @endphp

        <div class="card mb-5 mb-xl-10">
            <div class="card-header collapsible cursor-pointer rotate" data-bs-toggle="collapse" data-bs-target="#{{$column->column_name}}">
                <div class="card-title">{{$column->title}}</div>
                <div class="card-toolbar rotate-180">
                    <i class="ki-duotone ki-down fs-1"></i>
                </div>
            </div>
            <div id="{{$column->column_name}}" class="collapse show">
                <div class="card-body fs-4">
                    @if($formType->key == 'image')
                        @php
                            if ($multiple != 'multiple')
                            {
                                $images = [$inputValue];
                            }
                            else
                            {
                                $inputValue = html_entity_decode($inputValue);
                                $images     = json_decode($inputValue);
                            }
                        @endphp

                        <div class="row g-10 row-cols-2 row-cols-lg-5">
                            @foreach($images as $order => $image)
                                <div class="col">
                                    <a class="d-block overlay" data-fslightbox="lightbox-hot-sales" href="{{$image}}">
                                        <div class="overlay-wrapper bgi-no-repeat bgi-position-center bgi-size-cover card-rounded h-175px"
                                             style="background-image:url({{$image}}">
                                        </div>
                                        <div class="overlay-layer card-rounded bg-dark bg-opacity-25">
                                            <i class="ki-outline ki-eye fs-3x text-white"></i>
                                        </div>
                                    </a>
                                </div>
                            @endforeach
                        </div>
                    @else
                        {!! $inputValue !!}
                    @endif
                </div>
            </div>
        </div>
    @endforeach
@endsection