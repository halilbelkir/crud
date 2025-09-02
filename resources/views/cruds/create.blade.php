@extends('crudPackage::layout.main',['activePage' => $title.' Modülünü Ekle','parentPage' => 'Modüller','parentPageRoute' => route('cruds.index')])
@section('content')
    <div id="formResponse"></div>
    <form id="addUpdateForm" class="row m-0 p-0 justify-content-center " method="post" action="{{route('cruds.store')}}">
        <div class="card p-0">
            <div class="card-header bg-secondary">
                <h2 class="card-title">Modül Bilgisi</h2>
            </div>
            <div class="card-body py-4 form row justify-content-center fv-plugins-bootstrap5 fv-plugins-framework">
                <div class="form-group col-12 col-lg-4 mb-7 fv-plugins-icon-container">
                    <label class="required fw-semibold fs-6 mb-2">Menü Başlık</label>
                    <input type="text" value="{{$title}}" name="title" onkeyup="slug1(this.value,'#slug')" class="form-control form-control-solid mb-3 mb-lg-0" placeholder="Başlık">
                    <input type="hidden" name="table_name" value="{{$tableName}}">
                </div>

                <div class="form-group col-12 col-lg-4 mb-7 fv-plugins-icon-container">
                    <label class="required fw-semibold fs-6 mb-2">Link</label>
                    <input type="text" name="slug" value="{{$slug}}" id="slug" class="form-control form-control-solid mb-3 mb-lg-0" placeholder="Link">
                </div>

                <div class="form-group col-12 col-lg-4 mb-7 fv-plugins-icon-container">
                    <label class="required fw-semibold fs-6 mb-2">Tekli Sayfa Başlık</label>
                    <input type="text" value="{{ Str::headline(Str::singular($tableName))}}" name="display_single" class="form-control form-control-solid mb-3 mb-lg-0" placeholder="Tekli Sayfa Başlık">
                </div>

                <div class="form-group col-12 col-lg-4 mb-7 fv-plugins-icon-container">
                    <label class="required fw-semibold fs-6 mb-2">Çoklu Sayfa Başlık</label>
                    <input type="text" value="{{$title}}" name="display_plural" class="form-control form-control-solid mb-3 mb-lg-0" placeholder="Çoklu Sayfa Başlık">
                </div>

                <div class="form-group col-12 col-lg-4 mb-7 fv-plugins-icon-container">
                    <label class="fw-semibold fs-6 mb-2">
                        İkon
                        <i data-bs-toggle="tooltip" data-bs-placement="top" title='Örnek : <i class="bi bi-0-circle"></i>' data-bs-custom-class="tooltip-inverse" class="ki-outline color-primary fs-4 ki-information-5"></i>
                        (<small><a href="https://icons.getbootstrap.com/#content" target="_blank">İkon Kütüphanesi</a></small>)
                    </label>
                    <input name="icon" type="text" class="form-control form-control-solid" placeholder="İkon">
                </div>

                <div class="form-group col-12 col-lg-4 mb-7 fv-plugins-icon-container">
                    <label class="fw-semibold fs-6 mb-2">Model Dizini</label>
                    <input type="text" value="{{$modelName}}" name="model" class="form-control form-control-solid mb-3 mb-lg-0" placeholder="Model Dizini">
                </div>

                <div class="form-group col-12 col-lg-6 mb-7 fv-plugins-icon-container">
                    <label class="fw-semibold fs-6 mb-2">Sıralama Alanı</label>
                    <select name="order_column_name" data-control="select2" data-placeholder="Sıralama Alanı" data-allow-clear="true" class="form-control form-control-solid ">
                        <option value="">Seçiniz</option>
                        @foreach($columns as $column)
                            <option value="{{$column->name}}">{{$column->name}}</option>
                        @endforeach
                    </select>
                </div>

                <div class="form-group col-12 col-lg-6 mb-7 fv-plugins-icon-container">
                    <label class="fw-semibold fs-6 mb-2">Sıralama Yönü</label>
                    <select name="order_direction" class="form-control form-control-solid ">
                        <option value="">Seçiniz</option>
                        <option value="asc">Asc</option>
                        <option value="desc">Desc</option>
                    </select>
                </div>

                <div class="form-group col-12 mb-7 fv-plugins-icon-container">
                    <label class="required fw-semibold fs-6 mb-2">Açıklama</label>
                    <textarea name="content" cols="30" rows="3" placeholder="Açıklama" class="form-control form-control-solid mb-3 mb-lg-0"></textarea>
                </div>
            </div>
        </div>

        <div class="card mt-5 p-0">
            <div class="card-header bg-secondary">
                <h2 class="card-title">Modül Alanları</h2>
            </div>
            <div class="card-body form row justify-content-center fv-plugins-bootstrap5 fv-plugins-framework">
                <div id="columns">
                    <div class="form-group">
                        <div data-repeater-list="columns">
                            @foreach($columns as $columnKey => $column)
                                <div data-item-no="{{$columnKey}}" data-column-name="{{$column->name}}" data-repeater-item>
                                    <div class="form-group row">
                                        <div class="col-md-2 handle form-group">
                                            <i class="bi text-dark me-3 fs-4 bi-arrows-move"></i>
                                            <label class="fw-semibold fs-6 mb-2">Alan Adı : {{$column->name}}</label>
                                            <ul class="d-flex flex-column p-0">
                                                <li class="d-flex align-items-center py-2">
                                                    <strong class="me-3">Tipi :</strong> {{$column->type_name}}
                                                </li>
                                                <li class="d-flex align-items-center py-2">
                                                    <strong class="me-3">Boş :</strong>  {{$column->nullable ? 'Evet' : 'Hayır'}}
                                                </li>
                                            </ul>

                                            <input type="hidden" name="column_name" value="{{$column->name}}">
                                        </div>

                                        <div class="col-md-4">
                                            <div class="col-md-12 form-group">
                                                <label class="fw-semibold fs-6 mb-2">Görünecek İsim</label>
                                                <input name="title" type="text" value="{{$column->name}}" class="form-control form-control-solid" placeholder="Görünecek İsim">
                                            </div>

                                            <div class="col-md-12 form-group">
                                                <label class="fw-semibold fs-6 mb-2">Tip</label>
                                                <select name="form_type_id" class="form-control form-control-solid" @if($column->name == 'id' || $column->name == 'created_at' || $column->name == 'updated_at') disabled @endif>
                                                    <option value="">Seçiniz</option>
                                                    @foreach($formTypes as $type)
                                                        <option value="{{$type->id}}" @if($column->name == 'id' && $type->key == 'hidden' || $column->name == 'created_at' && $type->key == 'datetime' || $column->name == 'updated_at' && $type->key == 'datetime') selected @endif>{{$type->title}}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                        </div>

                                        <div class="col-md-2 form-group">
                                            <label class="fw-semibold fs-6 mb-2">Görünürlük</label>
                                            <div class="row align-items-center" style="padding: 0 1rem;">
                                                @foreach($visibilities as $visibilityKey =>  $visibility)
                                                    <div class="form-check form-check-sm mt-1 mb-2">
                                                        <input class="form-check-input visibility_{{$visibilityKey}}" id="visibility_{{$visibilityKey}}" name="{{$visibilityKey}}" type="checkbox" value="1"/>
                                                        <label class="form-check-label text-gray-700 fw-semibold" for="visibility_{{$visibilityKey}}">
                                                            {{$visibility}}
                                                        </label>
                                                    </div>
                                                @endforeach
                                            </div>
                                        </div>

                                        <div class="col-md-4 form-group">
                                            <label class="fw-semibold fs-6 mb-2">İsteğe Bağlı Ayrıntılar</label>
                                            <textarea id="{{$column->name}}" name="detail" data-json="true" rows="2" class="form-control form-control-solid">{}</textarea>
                                            <div id="{{$column->name}}_json_organizer" class="btn jsonOrganizerButton btn-secondary mt-3">JSON'u Düzenle</div>
                                        </div>

                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="pt-5 mt-5 col-lg-4 col-xl-2 text-center">
            <button type="submit" class="btn btn-primary buttonForm w-100"> Kaydet</button>
            @include('crudPackage::components.loading')
        </div>
    </form>
@endsection
@section('style')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.5/codemirror.min.css">
@endsection
@section('js')
    <script src="{{asset('crud/vendor/formrepeater/formrepeater.bundle.js')}}"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.5/codemirror.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.5/mode/javascript/javascript.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.5/addon/format/formatting.min.js"></script>
@endsection