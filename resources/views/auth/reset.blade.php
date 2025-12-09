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

        <div class="fv-row form-group mb-7">
            <input type="password" placeholder="Şifre" name="password"  class="form-control bg-transparent"/>
        </div>

        <div class="fv-row form-group mb-7">
            <input type="password" placeholder="Şifre Tekrarla" name="password_confirmation"  class="form-control bg-transparent"/>
        </div>

        <div>
            <button type="submit" class="buttonForm w-100 mt-4 btn btn-primary">
                <span class="indicator-label">Gönder</span>
            </button>
            @include('crudPackage::components.guest.loading')
        </div>
    </form>
@endsection