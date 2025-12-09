@extends('crudPackage::layout.guest')
@section('content')
    <form class="form row" method="post" id="login"  action="{{route('auth.password.forgot.send')}}">
        <div class="text-center g-3 mb-10">

            @include('crudPackage::components.guest.logo')

            <h1 class="text-dark fw-bolder">
                Şifremi Unuttum
            </h1>
        </div>

        <div class="fv-row form-group mb-8">
            <input type="email" placeholder="E-Mail" name="email" autocomplete="off" class="form-control bg-transparent"/>
        </div>
        <div class="d-flex justify-content-end mb-4">
            <a href="{{route('login')}}" class="link-primary">
                Giriş Yap
            </a>
        </div>
        <div>
            <button type="submit" class="buttonForm w-100 mt-4 btn btn-primary">
                <span class="indicator-label">Gönder</span>
            </button>
            @include('crudPackage::components.guest.loading')
        </div>
    </form>
@endsection