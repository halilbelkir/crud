@php $title = "Sayfa Bulunamadı"; @endphp
@extends('crudPackage::layout.main',['activePage' => $title])
@section('content')
    <div class="row align-items-center justify-content-center">
        <div class="col-lg-5 text-end">
            <img src="{{asset('crud/images/404.png')}}" class="w-50 w-lg-75">
        </div>
        <div class="col-lg-7">
            <h1>{{ $exception->getMessage() ? $exception->getMessage() : $title}}</h1>
            <p class="fs-2">Maalesef sayfa/dosya bulunamadı. Lütfen daha sonra tekrar deneyiniz.</p>
        </div>
    </div>
@endsection
