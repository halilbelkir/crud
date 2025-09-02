@extends('crudPackage::layout.main',['activePage' => $crud->display_single.' Kopyala','parentPage' => $crud->display_plural,'parentPageRoute' => route($crud->slug .'.index')])
@section('content')
    <div class="card">
        <div class="card-body py-4">
            <div id="formResponse"></div>
            <form id="addUpdateForm" class="row m-0 p-0 justify-content-center " method="post" action="{{route($crud->slug. '.store')}}">

                {!! $elements !!}

                <input type="hidden" name="crud_copy_id" value="{{$value->id}}">

                <div class="pt-5 col-lg-4 col-xl-2 text-center">
                    <button type="submit" class="btn btn-primary buttonForm w-100"> Kaydet</button>
                    @include('crudPackage::components.loading')
                </div>
            </form>
        </div>
    </div>
@endsection