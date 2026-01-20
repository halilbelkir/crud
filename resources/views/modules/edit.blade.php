@extends('crudPackage::layout.main',$breadcrumbs)
@section('content')
    @if($crud->content)
        <div class="alert alert-dismissible bg-secondary d-flex flex-column align-items-center flex-sm-row p-5 mb-10">
            <div class="fw-bold">{!! $crud->content !!}</div>
        </div>
    @endif
    <div class="card">
        <form id="addUpdateForm" class="form row m-0 p-0 justify-content-center " method="post" action="{{route($crud->slug .'.update',$value->id)}}">
            @method('PUT')
            <div class="card-header card-header-stretch">
                <div id="formResponse" class="mt-4"></div>
                <div class="card-toolbar">
                    @if(count(settings('languages')) > 0)
                        <ul class="nav nav-tabs nav-line-tabs nav-stretch fs-6 border-0">
                            @foreach(settings('languages') as $languageKey => $language)
                                <li class="nav-item">
                                    <a class="nav-link @if($languageKey == 0) active @endif" data-bs-toggle="tab" href="#{{ $language->code }}">{{ $language->title }}</a>
                                </li>
                            @endforeach
                        </ul>
                    @endif
                </div>
            </div>
            <div class="card-body card-scroll h-450px py-4">
                @if(count(settings('languages')) > 0)
                    <div class="tab-content" id="myTabContent">
                        @foreach(settings('languages') as $languageKey => $language)
                            <div class="tab-pane fade @if($languageKey == 0) show active @endif " id="{{ $language->code }}" role="tabpanel">
                                {!! $elementTabs[$language->code] !!}
                            </div>
                        @endforeach
                    </div>
                @else
                    {!! $elements !!}
                @endif

                <div class="clear"></div>

            </div>
            <div class="card-footer justify-content-center d-flex p-3">
                <div class="col-lg-4 col-xl-2 text-center">
                    <button type="submit" class="btn btn-primary buttonForm w-100"> Kaydet</button>
                    @include('crudPackage::components.loading')
                </div>
            </div>
        </form>

    </div>
@endsection
@section('js')
    <script src="{{asset('crud/vendor/formRepeater/formRepeater.bundle.js')}}"></script>
@endsection