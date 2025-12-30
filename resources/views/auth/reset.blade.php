@extends('crudPackage::layout.guest')
@section('content')
    <form class="form row" method="post" id="login"  action="{{route('auth.password.reset.update')}}">
        <div class="text-center g-3 mb-10">

            @include('crudPackage::components.guest.logo')

            <h1 class="text-dark fw-bolder">
                Şifreni Sıfırla
            </h1>
        </div>

        <input type="hidden" name="token" value="{{ request()->get('token') }}">

        <div class="fv-rowform-group  mb-8">
            <input type="email" placeholder="E-Mail" name="email" autocomplete="off" class="form-control bg-transparent"/>
        </div>

        <div class="fv-row form-group position-relative mb-3" data-kt-password-meter="true">
            <input type="password" placeholder="Şifre" name="password"  class="form-control bg-transparent"/>
            <span class="btn btn-sm btn-icon position-absolute translate-middle top-50 end-0 me-n2" data-kt-password-meter-control="visibility">
                <i class="bi bi-eye-fill fs-1 d-none"></i>
                <i class="bi bi-eye-slash-fill fs-1"></i>
            </span>
        </div>

        <div class="fv-row form-group position-relative mb-3" data-kt-password-meter="true">
            <input type="password" placeholder="Şifre Tekrarla" name="password_confirmation"  class="form-control bg-transparent"/>
            <span class="btn btn-sm btn-icon position-absolute translate-middle top-50 end-0 me-n2" data-kt-password-meter-control="visibility">
                <i class="bi bi-eye-fill fs-1 d-none"></i>
                <i class="bi bi-eye-slash-fill fs-1"></i>
            </span>
        </div>

        <div>
            <button type="submit" class="buttonForm w-100 mt-4 btn btn-primary">
                <span class="indicator-label">Gönder</span>
            </button>
            @include('crudPackage::components.guest.loading')
        </div>
    </form>
@endsection