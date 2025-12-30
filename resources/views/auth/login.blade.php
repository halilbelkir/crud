@extends('crudPackage::layout.guest')
@section('content')
    <form class="form row" method="post" id="login"  action="{{route('auth.login.store')}}">
        <div class="text-center g-3 mb-10">

            @include('crudPackage::components.guest.logo')

            <h1 class="text-dark fw-bolder">
                Giriş Yap
            </h1>
        </div>

        <div class="fv-row form-group mb-8">
            <input type="email" placeholder="E-Mail" name="email" autocomplete="off" class="form-control bg-transparent"/>
        </div>
        <div class="fv-row form-group position-relative mb-3" data-kt-password-meter="true">
            <input type="password" placeholder="Şifre" name="password" autocomplete="off" class="form-control bg-transparent"/>
            <span class="btn btn-sm btn-icon position-absolute translate-middle top-50 end-0 me-n2" data-kt-password-meter-control="visibility">
                <i class="bi bi-eye-fill fs-1 d-none"></i>
                <i class="bi bi-eye-slash-fill fs-1"></i>
            </span>
        </div>
        <div class="d-flex justify-content-end mb-4">
            <a href="{{route('auth.password.forgot')}}" class="link-primary">
                Şifremi Unuttum
            </a>
        </div>
        <div>
            <button type="submit" class="buttonForm w-100 mt-4 btn btn-primary">
                <span class="indicator-label">Giriş Yap</span>
            </button>
            @include('crudPackage::components.guest.loading')
        </div>
    </form>
@endsection