@extends('crudPackage::layout.main',['activePage' => 'Profilim'])
@section('content')
    <div class="card">
        <div class="card-body py-4">
            <form id="addUpdateForm" class="form row justify-content-center fv-plugins-bootstrap5 fv-plugins-framework"
                  method="post" action="{{route('profile.update')}}">
                @method('PUT')
                <div class="form-group col-12 col-lg-6 mb-7 fv-plugins-icon-container">
                    <label class="required fw-semibold fs-6 mb-2">Ad & Soyad</label>
                    <input type="text" value="{{$value->name ?? null}}" name="name"
                           class="form-control form-control-solid mb-3 mb-lg-0" placeholder="Ad & Soyad">
                </div>

                <div class="form-group col-12 col-lg-6 mb-7 fv-plugins-icon-container">
                    <label class="fw-semibold fs-6 mb-2">E-Mail</label>
                    <input type="email" value="{{$value->email ?? null}}" disabled
                           class="form-control form-control-solid mb-3 mb-lg-0" placeholder="E-Mail">
                </div>

                <div class="separator separator-dashed my-6"></div>

                <div class="form-group col-12 mb-7 fv-plugins-icon-container">
                    <label class="fw-semibold fs-6 mb-2">Mevcut Şifre</label>
                    <div class="position-relative" data-kt-password-meter="true">
                        <input type="password" name="current_password" autocomplete="current-password"
                               class="form-control form-control-solid" placeholder="Mevcut Şifre">
                        <span class="btn btn-sm btn-icon position-absolute translate-middle top-50 end-0 me-n2" data-kt-password-meter-control="visibility">
                            <i class="bi bi-eye-fill fs-1 d-none"></i>
                            <i class="bi bi-eye-slash-fill fs-1"></i>
                        </span>
                    </div>
                    <div class="text-muted fs-7 mt-2">Şifrenizi değiştirmek istemiyorsanız şifre alanlarını boş bırakınız.</div>
                </div>

                <div class="form-group col-12 col-lg-6 mb-7 fv-plugins-icon-container">
                    <label class="fw-semibold fs-6 mb-2">Yeni Şifre</label>
                    <div class="position-relative" data-kt-password-meter="true">
                        <input type="password" name="password" autocomplete="new-password"
                               class="form-control form-control-solid" placeholder="Yeni Şifre">
                        <span class="btn btn-sm btn-icon position-absolute translate-middle top-50 end-0 me-n2" data-kt-password-meter-control="visibility">
                            <i class="bi bi-eye-fill fs-1 d-none"></i>
                            <i class="bi bi-eye-slash-fill fs-1"></i>
                        </span>
                    </div>
                </div>

                <div class="form-group col-12 col-lg-6 mb-7 fv-plugins-icon-container">
                    <label class="fw-semibold fs-6 mb-2">Yeni Şifre (Tekrar)</label>
                    <div class="position-relative" data-kt-password-meter="true">
                        <input type="password" name="password_confirmation" autocomplete="new-password"
                               class="form-control form-control-solid" placeholder="Yeni Şifre (Tekrar)">
                        <span class="btn btn-sm btn-icon position-absolute translate-middle top-50 end-0 me-n2" data-kt-password-meter-control="visibility">
                            <i class="bi bi-eye-fill fs-1 d-none"></i>
                            <i class="bi bi-eye-slash-fill fs-1"></i>
                        </span>
                    </div>
                </div>

                <div class="pt-5 col-lg-4 col-xl-2 text-center">
                    <button type="submit" class="btn btn-primary buttonForm w-100"> Kaydet</button>
                    @include('crudPackage::components.loading')
                </div>
            </form>
        </div>
    </div>
@endsection
