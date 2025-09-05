@extends('crudPackage::layout.main',['activePage' => $crud->display_single.' Düzenle','parentPage' => $crud->display_plural,'parentPageRoute' => route($crud->slug .'.index')])
@section('content')
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
    <script src="{{asset('crud/vendor/formrepeater/formrepeater.bundle.js')}}"></script>
@endsection