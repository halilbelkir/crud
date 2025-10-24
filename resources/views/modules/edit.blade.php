@extends('crudPackage::layout.main',$breadcrumbs)
@section('content')
    @if($crud->content)
        <div class="alert alert-dismissible bg-secondary d-flex flex-column align-items-center flex-sm-row p-5 mb-10">
            <div class="fw-bold">{!! $crud->content !!}</div>
        </div>
    @endif
    <div class="card">
        <div class="card-body py-4">
            <div id="formResponse"></div>
            <form id="addUpdateForm" class="form row justify-content-center fv-plugins-bootstrap5 fv-plugins-framework"
                  method="post" action="{{route($crud->slug .'.update',$value->id)}}">
                @method('PUT')

                {!! $elements !!}

                <div class="pt-5 col-lg-4 col-xl-2 text-center">
                    <button type="submit" class="btn btn-primary buttonForm w-100"> Kaydet</button>
                    @include('crudPackage::components.loading')
                </div>
            </form>
        </div>
    </div>
@endsection
@section('js')
    <script src="{{asset('crud/vendor/formRepeater/formRepeater.bundle.js')}}"></script>
@endsection