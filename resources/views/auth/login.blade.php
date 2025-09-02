@extends('crudPackage::layout.guest')
@section('content')
    <div class="d-flex flex-column flex-lg-row flex-column-fluid justify-content-center align-items-center">
        <div class="d-flex flex-lg-row-fluid">
            <div class="d-flex flex-column flex-center pb-0 pb-lg-10 p-10 w-100">
                <img class="theme-light-show mx-auto mw-100 w-150px w-lg-400px mb-10 mb-lg-20" src="{{asset('crud/images/guest.svg')}}" alt=""/>
                <img class="theme-dark-show mx-auto mw-100 w-150px w-lg-400px mb-10 mb-lg-20" src="{{asset('crud/images/guest.svg')}}" alt=""/>

                <h1 class="text-gray-800 fs-2qx fw-bold text-center mb-7">
                    Zaurac Teknoloji Admin Paneline <br> Hoş Geldiniz
                </h1>
            </div>
        </div>
        <div class="d-flex flex-column-fluid flex-lg-row-auto justify-content-center justify-content-lg-end py-12 px-lg-12">
            <div class="bg-body d-flex flex-column flex-center rounded-4 w-lg-600px p-10">
                <div class="d-flex flex-center flex-column align-items-stretch w-lg-400px">
                    <div class="d-flex flex-center flex-column-fluid pb-15 pb-lg-20">
                        <form class="form row" method="post" id="login"  action="{{route('auth.login.store')}}">
                            <div class="text-center g-3 mb-10">
                                <h1 class="text-gray-800 fs-2qx fw-bold text-center mb-10">
                                    <img src="{{asset('crud/images/logo.svg')}}" style="height: 60px" alt="">
                                </h1>

                                <h1 class="text-dark fw-bolder">
                                    Giriş Yap
                                </h1>
                            </div>

                            <div class="fv-row form-group mb-8">
                                <input type="email" placeholder="E-Mail" name="email" autocomplete="off" class="form-control bg-transparent"/>
                            </div>
                            <div class="fv-row form-group mb-3">
                                <input type="password" placeholder="Şifre" name="password" autocomplete="off" class="form-control bg-transparent"/>
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
                                <div class="loading justify-content-center d-flex d-none" style="height: 50px">
                                    <img src="{{asset('crud/images/loading.gif')}}" alt="">
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
                <!--end::Content-->
            </div>
            <!--end::Wrapper-->
        </div>
        <!--end::Body-->
    </div>
@endsection