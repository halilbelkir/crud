@extends('crudPackage::layout.main',['activePage' => $value->name.' Düzenle','parentPage' => 'Kullanıcılar','parentPageRoute' => route('users.index')])
@section('content')
    <div class="card">
        <div class="card-body py-4">
            <form id="addUpdateForm" class="form row justify-content-center fv-plugins-bootstrap5 fv-plugins-framework"
                  method="post" action="{{route('users.update',$value->id)}}">
                @method('PUT')
                <div class="form-group col-12 col-lg-6 mb-7 fv-plugins-icon-container">
                    <label class="required fw-semibold fs-6 mb-2">Ad & Soyad</label>
                    <input type="text" value="{{$value->name ?? null}}" name="name"
                           class="form-control form-control-solid mb-3 mb-lg-0" placeholder="Ad & Soyad">
                </div>

                <div class="form-group col-12 col-lg-6 mb-7 fv-plugins-icon-container">
                    <label class="required fw-semibold fs-6 mb-2">E-Mail</label>
                    <input type="email" value="{{$value->email ?? null}}" name="email"
                           class="form-control form-control-solid mb-3 mb-lg-0" placeholder="E-Mail">
                </div>

                <div class="form-group col-12 mb-7 fv-plugins-icon-container">
                    <label class="fw-semibold fs-6 mb-2">Yetkisi</label>
                    <select name="role_group_id" class="form-control form-control-solid mb-3 mb-lg-0">
                        <option value="">Seçiniz</option>
                        @foreach($roles as $role)
                            <option value="{{$role->id}}" @if($role->id == $value->role_group_id) selected @endif>{{$role->title}}</option>
                        @endforeach
                    </select>
                </div>

                <div class="pt-5 col-lg-4 col-xl-2 text-center">
                    <button type="submit" class="btn btn-primary buttonForm w-100"> Kaydet</button>
                    @include('crudPackage::components.loading')
                </div>
            </form>
        </div>
    </div>
@endsection