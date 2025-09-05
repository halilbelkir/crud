@extends('crudPackage::layout.main',['activePage' => $crud->display_single.' Detay','parentPage' => $crud->display_plural,'parentPageRoute' => route($crud->slug .'.index')])
@section('content')
    @foreach($crud->readColumns as $column)
        @php
            $formType   = $column->type;
            $details    = json_decode($column->detail,true);
            $multiple   = isset($details['multiple']) && $details['multiple'] == true ? 'multiple' : null;

            if ($column->repeater == 1)
            {
                $inputValue = html_entity_decode($values->{$column->column_name});
                $inputValue = json_decode($inputValue);
            }
            else
            {
                $inputValue = $values->{$column->column_name};
            }
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
                    @elseif($column->repeater == 1)
                        <div class="table-responsive">
                            <table class="table align-middle table-row-dashed fs-6 gy-4 dataTable no-footer">
                                <thead>
                                    <tr class="text-start text-muted fw-bold fs-7 text-uppercase gs-0">
                                        @foreach($details as $detail)
                                            <td>{{$detail['title']}}</td>
                                        @endforeach
                                    </tr>
                                </thead>
                                @foreach($inputValue as $value)
                                    <tr>
                                        @foreach($details as $detail)
                                            @if($detail['form_type_id'] == 3)
                                                <td>{{ \Carbon\Carbon::make($value->{$detail['column_name']})->format('d.m.Y') }}</td>
                                            @elseif($detail['form_type_id'] == 4)
                                                <td>{{ \Carbon\Carbon::make($value->{$detail['column_name']})->format('d.m.Y H:i:s') }}</td>
                                            @else
                                                <td>{!! $value->{$detail['column_name']} !!}</td>
                                            @endif
                                        @endforeach
                                    </tr>
                                @endforeach
                            </table>
                        </div>
                    @else
                        {!! $inputValue !!}
                    @endif
                </div>
            </div>
        </div>
    @endforeach
@endsection