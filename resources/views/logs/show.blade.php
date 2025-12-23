@extends('crudPackage::layout.main',['activePage' => $value->log_name.' Detay','parentPage' => 'Geçmiş','parentPageRoute' => route('logs.index')])
@section('content')
    <div class="card mb-5 mb-xl-10">
        <div class="card-body pt-9 pb-0">
            <div class="d-flex flex-wrap flex-sm-nowrap">
                <div class="flex-grow-1">
                    <div class="d-flex justify-content-between align-items-start flex-wrap mb-2">
                        <div class="d-flex flex-column">
                            <div class="d-flex align-items-center mb-2">
                                <a href="#" class="text-gray-900 text-hover-primary fs-2 fw-bold me-1">{{$value->log_name . '-' . $value->subject_id}}</a>
                            </div>
                            {!! $message !!}
                            <div class="text-decoration-underline fw-bold mt-5">İşlemi Yapan</div>
                            <div class="text-gray-600">{{$user->name ?? null}}</div>
                            <div class="text-decoration-underline fw-bold mt-5">İşlem Zamanı</div>
                            <div class="text-gray-600">{{\Carbon\Carbon::make($value->created_at)->format('d-m-Y H:i:s')}}</div>
                        </div>
                    </div>
                </div>
            </div>

            <ul class="nav nav-stretch nav-line-tabs nav-line-tabs-2x border-transparent fs-5 fw-bold">
                <li class="nav-item mt-2">
                    <a class="nav-link text-active-primary ms-0 me-10 py-5 active"  data-bs-toggle="tab" href="#columns">
                        Alanlar
                    </a>
                </li>
            </ul>
        </div>
    </div>

    <div class="tab-content" id="myTabContent">
        @include('crudPackage::logs.overview')
    </div>
@endsection