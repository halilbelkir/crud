@extends('crudPackage::layout.main',['activePage' => $value->title.' Düzenle','parentPage' => 'Menüler','parentPageRoute' => route('menus.index')])
@section('content')
    <div class="card">
        <div class="card-body py-4">
            <form id="addUpdateForm" class="form row justify-content-center fv-plugins-bootstrap5 fv-plugins-framework"
                  method="post" action="{{route('menus.update',$value->id)}}">
                @method('PUT')
                <div class="form-group col-12 mb-7 fv-plugins-icon-container">
                    <label class="required fw-semibold fs-6 mb-2">Başlık</label>
                    <input type="text" name="title"
                           value="{{$value->title}}"
                           class="form-control form-control-solid mb-3 mb-lg-0"
                           placeholder="Başlık">
                </div>

                <div id="items">

                    <div class="separator separator-content border-dark my-15"><span class="w-250px h2">Menü Linkleri</span></div>
                    <div class="form-group">
                        <div data-repeater-list="items">
                            @foreach($value->items as $key => $item)

                                @php $itemCrud = null; @endphp

                                @if($item->dynamic_route == 1)
                                    @php
                                        $route    = $item->route;
                                        $route    = explode('.',$route);
                                        $route    = $route[0];
                                        $itemCrud = $item->crud($route);
                                    @endphp
                                @endif

                                <div data-item-no="{{$key}}" data-repeater-item>
                                    <div class="form-group row">
                                        <div class="col-md-2 form-group">
                                            <label class="fw-semibold fs-6 mb-2">Ekranlar</label>
                                            <select onchange="getCrud(this)" name="item" data-action="{{route('single.crud')}}" class="form-control form-control-solid">
                                                <option value="">Seçiniz</option>
                                                @foreach($cruds as $crud)
                                                    <option value="{{$crud->id}}" @if(isset($itemCrud) && $itemCrud->id == $crud->id) selected @endif>{{$crud->title}}</option>
                                                @endforeach
                                            </select>
                                        </div>

                                        <div class="col-md-3 form-group">
                                            <label class="fw-semibold fs-6 mb-2">Başlık</label>
                                            <input name="title" type="text" class="form-control form-control-solid" value="{{$item->title}}" placeholder="Başlık">
                                        </div>

                                        <div class="col-md-2 form-group">
                                            <label class="fw-semibold fs-6 mb-2">Url / Route</label>
                                            <input name="route" type="text" class="form-control form-control-solid" value="{{$item->route}}" placeholder="Url / Route">
                                        </div>

                                        <div class="col-md-2 form-group">
                                            <label class="fw-semibold fs-6 mb-2">
                                                İkon
                                                <i data-bs-toggle="tooltip" data-bs-placement="top" title='Örnek : <i class="bi bi-0-circle"></i>' data-bs-custom-class="tooltip-inverse" class="ki-outline color-primary fs-4 ki-information-5"></i>
                                                (<small><a href="https://icons.getbootstrap.com/#content" target="_blank">İkon Kütüphanesi</a></small>)
                                            </label>
                                            <input name="icon" type="text" class="form-control form-control-solid" value="{{$item->icon}}" placeholder="İkon">
                                        </div>

                                        <div class="col-12 col-lg-1 pb-4">
                                            <label class="form-check form-switch form-switch-sm form-check-custom form-check-solid flex-stack d-block">
                                                <div class="form-check-label text-gray-700 w-100 mx-0 fs-6 fw-semibold mb-2">
                                                    Yeni Sekme
                                                </div>
                                                <input class="form-check-input mt-4" name="target" type="checkbox" value="1" @if($item->target == 1) checked @endif />
                                            </label>
                                        </div>

                                        <div class="col-12 col-lg-1 pb-4">
                                            <label class="form-check form-switch form-switch-sm form-check-custom form-check-solid flex-stack d-block">
                                                <div class="form-check-label text-gray-700 w-100 mx-0 fs-6 fw-semibold mb-2">
                                                    Dinamik Route
                                                </div>
                                                <input class="form-check-input mt-4" name="dynamic_route" type="checkbox" value="1" @if($item->dynamic_route == 1) checked @endif />
                                            </label>
                                        </div>

                                        <input type="hidden" name="id" value="{{$item->id}}">
                                        @if($item->main == 0)
                                            <div class="col-md-1 form-group">
                                                <a href="javascript:;" data-repeater-delete class="btn btn-flex btn-tertiary mt-6">
                                                    <i class="ki-outline ki-trash  fs-3"></i>
                                                    Sil
                                                </a>
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                    <div class="form-group">
                        <a href="javascript:;" data-repeater-create class="btn btn-flex btn-light-primary">
                            <i class="ki-duotone ki-plus fs-3"></i>
                            Ekle
                        </a>
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
@section('js')
    <script src="https://code.jquery.com/ui/1.14.1/jquery-ui.js"></script>
    <script src="{{asset('crud/vendor/formrepeater/formrepeater.bundle.js')}}"></script>
@endsection