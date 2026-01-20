@extends('crudPackage::layout.main',['activePage' => $crud->display_single.' Detay','parentPage' => $crud->display_plural,'parentPageRoute' => route($crud->slug .'.index')])
@section('content')
    @if(count(settings('languages')) > 0)
        <div class="card mb-5">
            <div class="card-header card-header-stretch">
                <div id="formResponse" class="mt-4"></div>
                <div class="card-toolbar">
                    <ul class="nav nav-tabs nav-line-tabs nav-stretch fs-6 border-0">
                        @foreach(settings('languages') as $languageKey => $language)
                            <li class="nav-item">
                                <a class="nav-link @if($languageKey == 0) active @endif" data-bs-toggle="tab" href="#{{ $language->code }}">{{ $language->title }}</a>
                            </li>
                        @endforeach
                    </ul>
                </div>
            </div>
        </div>

        <div class="tab-content" id="myTabContent">
            @foreach(settings('languages') as $languageKey => $language)
                <div class="tab-pane fade @if($languageKey == 0) show active @endif " id="{{ $language->code }}" role="tabpanel">
                    @include('crudPackage::modules.read',['values' => $elementTabs[$language->code],'language' => $language])
                </div>
            @endforeach
        </div>
    @else
        @include('crudPackage::modules.read')
    @endif

@endsection