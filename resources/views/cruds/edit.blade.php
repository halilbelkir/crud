@extends('crudPackage::layout.main',['activePage' => $value->title.' Düzenle','parentPage' => 'Modüller','parentPageRoute' => route('cruds.index')])
@section('content')
    <div id="formResponse"></div>
    <form id="addUpdateForm" class="row m-0 p-0 justify-content-center " method="post" action="{{route('cruds.update',$value->id)}}">
        @method('PUT')
        <div class="card p-0">
            <div class="card-header bg-secondary">
                <h2 class="card-title">Modül Bilgisi</h2>
            </div>
            <div class="card-body py-4 form row justify-content-center fv-plugins-bootstrap5 fv-plugins-framework">
                <div class="form-group col-12 col-lg-4">
                    <label class="required fw-semibold fs-6 mb-2">Menü Başlık</label>
                    <input type="text" value="{{$value->title}}" name="title" onkeyup="slug1(this.value,'#slug')" class="form-control form-control-solid mb-3 mb-lg-0" placeholder="Başlık">
                    <input type="hidden" name="table_name" value="{{$value->table_name}}">
                </div>

                <div class="form-group col-12 col-lg-4">
                    <label class="required fw-semibold fs-6 mb-2">Link</label>
                    <input type="text" name="slug" value="{{$value->slug}}" id="slug" class="form-control form-control-solid mb-3 mb-lg-0" placeholder="Link">
                </div>

                <div class="form-group col-12 col-lg-4">
                    <label class="required fw-semibold fs-6 mb-2">Tekli Sayfa Başlık</label>
                    <input type="text" value="{{ $value->display_single }}" name="display_single" class="form-control form-control-solid mb-3 mb-lg-0" placeholder="Tekli Sayfa Başlık">
                </div>

                <div class="form-group col-12 col-lg-4">
                    <label class="required fw-semibold fs-6 mb-2">Çoklu Sayfa Başlık</label>
                    <input type="text" value="{{ $value->display_plural }}" name="display_plural" class="form-control form-control-solid mb-3 mb-lg-0" placeholder="Çoklu Sayfa Başlık">
                </div>

                <div class="form-group col-12 col-lg-4">
                    <label class="fw-semibold fs-6 mb-2">
                        İkon
                        <i data-bs-toggle="tooltip" data-bs-placement="top" title='Örnek : <i class="bi bi-0-circle"></i>' data-bs-custom-class="tooltip-inverse" class="ki-outline color-primary fs-4 ki-information-5"></i>
                        (<small><a href="https://icons.getbootstrap.com/#content" target="_blank">İkon Kütüphanesi</a></small>)
                    </label>
                    <input name="icon" type="text" value="{{ $value->icon }}" class="form-control form-control-solid" placeholder="İkon">
                </div>

                <div class="form-group col-12 col-lg-4">
                    <label class="required fw-semibold fs-6 mb-2">Model Dizini</label>
                    <input type="text" value="{{$value->model}}" name="model" class="form-control form-control-solid mb-3 mb-lg-0" placeholder="Model Dizini">
                </div>

                <div class="form-group col-12 col-lg-6 mb-7 fv-plugins-icon-container">
                    <label class="fw-semibold fs-6 mb-2">Sıralama Alanı</label>
                    <select name="order_column_name" data-control="select2" data-placeholder="Sıralama Alanı" data-allow-clear="true" class="form-control form-control-solid ">
                        <option value="">Seçiniz</option>
                        @foreach($columns as $column)
                            <option value="{{$column->name}}" @if(isset($area1) && $area1->order_column_name == $column->name) selected @endif>{{$column->name}}</option>
                        @endforeach
                    </select>
                </div>

                <div class="form-group col-12 col-lg-6 mb-7 fv-plugins-icon-container">
                    <label class="fw-semibold fs-6 mb-2">Sıralama Yönü</label>
                    <select name="order_direction" class="form-control form-control-solid ">
                        <option value="">Seçiniz</option>
                        <option value="asc" @if(isset($area1) && $area1->order_direction == 'asc') selected @endif>Asc</option>
                        <option value="desc" @if(isset($area1) && $area1->order_direction == 'desc') selected @endif>Desc</option>
                    </select>
                </div>

                <div class="form-group col-12">
                    <label class="required fw-semibold fs-6 mb-2">Açıklama</label>
                    <textarea name="content" cols="30" rows="3" placeholder="Açıklama" class="form-control form-control-solid mb-3 mb-lg-0">{{$value->content}}</textarea>
                </div>

                <div class="form-group col-12 mb-7 fv-plugins-icon-container">
                    <div class="form-check form-check-sm mb-3">
                        <input class="form-check-input only_edit"
                               id="only_edit"
                               name="only_edit"
                               type="checkbox"
                               value="1"
                               @if($value->only_edit == 1) checked @endif
                        />
                        <label class="form-check-label text-gray-700 fw-semibold" for="only_edit">
                            Sadece Düzenleme Adımı Olsun
                        </label>
                    </div>
                </div>
            </div>
        </div>

        <div class="card mt-5 p-0">
            <div class="card-header bg-secondary">
                <h2 class="card-title">Modül Alanları</h2>
            </div>
            <div class="card-body px-0 pb-0 form row justify-content-center fv-plugins-bootstrap5 fv-plugins-framework">
                <div id="columns">
                    <div data-repeater-list="columns">
                        @foreach($newColumns as $columnKey => $newColumn)
                            @php
                                $column = $newColumn['attribute'];
                                $item   = $newColumn['item'] ?? null;

                                if (empty($item))
                                {
                                    $item = (object) [ 'relationship' => 0 ,'repeater' => 0 , 'id' => null ,'form_type_id' => null];
                                }
                            @endphp

                            <div data-item-no="{{$columnKey}}" @if($item->relationship == 1 || $item->repeater == 1) class="ribbon ribbon-start ribbon-clip" @endif data-column-name="{{$column->name}}" data-repeater-item>
                                @if($item->relationship == 1 || $item->repeater == 1)
                                    <div class="ribbon-label">
                                        @if($item->relationship == 1) İlişkili @else Tekrarlanan Alan @endif
                                        <span class="ribbon-inner bg-info"></span>
                                    </div>
                                @endif
                                <div class="form-group row">
                                    <div class="col-md-2 handle form-group">
                                        <i class="bi text-dark me-3 fs-4 bi-arrows-move"></i>
                                        <label class="fw-semibold fs-6 mb-2 d-block">Alan Adı : {{$column->name}}</label>
                                        <ul class="d-flex flex-column p-0">
                                            <li class="d-flex align-items-center py-2">
                                                <strong class="me-3">Tipi :</strong> {{$column->type_name}}
                                            </li>
                                            <li class="d-flex align-items-center py-2">
                                                <strong class="me-3">Boş :</strong>  {{$column->nullable ? 'Evet' : 'Hayır'}}
                                            </li>
                                        </ul>

                                        @if($item->relationship == 1)
                                            <a data-route="{{route('cruds.relationship.destroy',$item->id)}}" onclick="destroy(this)" data-title="{{$item->title.' isimli ilişkiyi'}}" class="btn btn-flex btn-tertiary">
                                                <i class="ki-outline ki-trash fs-3"></i>
                                                Sil
                                            </a>
                                        @elseif($item->repeater == 1)
                                            <a data-route="{{route('cruds.repeater.destroy',$item->id)}}" onclick="destroy(this)" data-title="{{$item->title.' isimli tekrarlanan alanı'}}" class="btn btn-flex btn-tertiary">
                                                <i class="ki-outline ki-trash fs-3"></i>
                                                Sil
                                            </a>
                                        @endif

                                        <input type="hidden" name="column_name" value="{{$column->name}}">
                                        <input type="hidden" name="id" value="{{$item->id}}">
                                    </div>

                                    <div class="col-md-4">
                                        <div class="col-md-12 form-group">
                                            <label class="fw-semibold fs-6 mb-2">Görünecek İsim</label>
                                            <input name="title" type="text" value="{{$item->title ?? $column->name}}" class="form-control form-control-solid" placeholder="Görünecek İsim">
                                        </div>

                                        <div class="col-md-12 form-group">
                                            <label class="fw-semibold fs-6 mb-2">Tip</label>
                                            <select name="form_type_id" class="form-control form-control-solid" @if($column->name == 'created_at' || $column->name == 'id' || $column->name == 'updated_at' || $item->relationship == 1 || $item->repeater == 1) disabled @endif>
                                                <option value="">Seçiniz</option>
                                                @foreach($formTypes as $type)
                                                    <option value="{{$type->id}}" @if($item->form_type_id == $type->id || $column->name == 'id' && $type->key == 'hidden' || $column->name == 'created_at' && $type->key == 'datetime' || $column->name == 'updated_at' && $type->key == 'datetime') selected @endif>{{$type->title}}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>

                                    <div class="col-md-2 form-group">
                                        <label class="fw-semibold fs-6 mb-2">Görünürlük</label>
                                        <div class="row align-items-center" style="padding: 0 1rem;">
                                            @foreach($visibilities as $visibilityKey =>  $visibility)
                                                <div class="form-check form-check-sm mt-1 mb-2">
                                                    <input class="form-check-input visibility_{{$visibilityKey}}" id="visibility_{{$visibilityKey}}" name="{{$visibilityKey}}" type="checkbox" @if(isset($item->$visibilityKey) && $item->$visibilityKey == 1) checked @endif value="1"/>
                                                    <label class="form-check-label text-gray-700 fw-semibold" for="visibility_{{$visibilityKey}}">
                                                        {{$visibility}}
                                                    </label>
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>

                                    @php
                                        $jsonOrganizerButton   = $item->relationship == 1 ? 'json_organizer_relationship_'.$column->name : $column->name.'_json_organizer';
                                        $jsonOrganizerTextArea = $item->relationship == 1 ? 'detail_relationship_'.$column->name : $column->name;
                                    @endphp

                                    <div class="col-md-4 form-group">
                                        <label class="fw-semibold fs-6 mb-2">İsteğe Bağlı Ayrıntılar</label>
                                        <textarea id="{{$jsonOrganizerTextArea}}" name="detail" data-json="true" rows="2" class="form-control form-control-solid">{{$item->detail ?? '{}'}}</textarea>
                                        <div id="{{$jsonOrganizerButton}}" class="btn jsonOrganizerButton btn-secondary mt-3">JSON'u Düzenle</div>
                                    </div>

                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>

            <div class="card-footer">
                <button type="button" class="btn btn-info" data-bs-toggle="modal" data-bs-target="#insertModal">
                    İlişki Ekle
                </button>

                <button type="button" class="btn btn-tertiary ms-2 fw-normal" data-bs-toggle="modal" data-bs-target="#repeaterModal">
                    Tekrarlanan Alan Ekle
                </button>
            </div>
        </div>

        <div class="pt-5 mt-5 col-lg-4 col-xl-2 text-center">
            <button type="submit" class="btn btn-primary buttonForm w-100"> Kaydet</button>
            @include('crudPackage::components.loading')
        </div>
    </form>

    <div class="modal fade modal-xl" data-bs-backdrop="static" data-bs-keyboard="false" id="insertModal"
         aria-hidden="true" style="display: none;">
        <div class="modal-dialog modal-dialog-centered ">
            <div class="modal-content">
                <div class="modal-header" id="repeaterModalHeader">
                    <h2 class="fw-bold">İlişki Ekle</h2>
                    <div class="btn btn-icon btn-sm btn-active-icon-primary" data-dismiss="modal">
                        <i class="ki-duotone ki-cross fs-1"><span class="path1"></span><span
                                    class="path2"></span></i>
                    </div>
                </div>

                <div class="modal-body scroll-y mx-5 mx-xl-10">
                    <form id="relationshipForm" class="form  fv-plugins-bootstrap5 fv-plugins-framework"
                          method="post" action="{{route('cruds.relationship.store',$value->id)}}">
                        @method('PUT')
                        <div class="row scroll-y me-n7 pe-7" id="repeaterModalScroll"
                             data-kt-scroll="true" data-kt-scroll-activate="{default: false, lg: true}"
                             data-kt-scroll-max-height="auto"
                             data-kt-scroll-dependencies="#repeaterModalHeader"
                             data-kt-scroll-wrappers="#repeaterModalScroll"
                             data-kt-scroll-offset="300px" style="max-height: 281px;">


                            <div class="col-12 form-group">
                                <label class="required fw-semibold fs-6 mb-2">Görünecek İsim</label>
                                <input name="relationship_title" type="text" class="form-control form-control-solid" placeholder="Görünecek İsim">
                            </div>

                            <div class="form-group col-12 col-lg-6">
                                <label class="required fw-semibold fs-6 mb-2">İlişki</label>
                                <select name="relationship" onchange="relationshipPivotTable(this)" class="form-control form-control-solid">
                                    <option value="">Seçiniz</option>
                                    <option value="hasOne">Has One</option>
                                    <option value="hasMany">Has Many</option>
                                    <option value="belongsTo">Belongs To</option>
                                    <option value="belongsToMany">Belongs To Many</option>
                                </select>
                            </div>

                            <div class="form-group col-12 col-lg-6 d-none" id="relationship_pivot_table">
                                <label class="required fw-semibold fs-6 mb-2">Pivot Tablo</label>
                                <select name="relationship_pivot_table_name" data-control="select2" data-placeholder="Pivot Tablo Seçiniz" data-allow-clear="true" class="form-control form-control-solid ">
                                    <option value="">Seçiniz</option>
                                    @foreach($tables as $table)
                                        <option value="{{$table->title}}">{{$table->title}}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="form-group col-12 col-lg-6" id="relationship_column_name">
                                <label class="required fw-semibold fs-6 mb-2">Referans Alınacak Alan</label>
                                <select name="relationship_column_name" data-control="select2" data-placeholder="Referans Alınacak Alan Seçiniz" data-allow-clear="true" class="form-control form-control-solid ">
                                    <option value="">Seçiniz</option>
                                    @foreach($columns as $column)
                                        <option value="{{$column->name}}">{{$column->name}}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="form-group col-12 col-lg-6">
                                <label class="required fw-semibold fs-6 mb-2">Referans Tablo</label>
                                <select onchange="getColumns(this)" name="relationship_table_name" data-control="select2" data-placeholder="Tablo Seçiniz" data-allow-clear="true" class="form-control form-control-solid ">
                                    <option value="">Seçiniz</option>
                                    @foreach($tables as $table)
                                        <option value="{{$table->title}}">{{$table->title}}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="form-group col-12 col-lg-6">
                                <label class="required fw-semibold fs-6 mb-2">Referans Tablo Model</label>
                                <input name="relationship_table_model" type="text" class="form-control form-control-solid" placeholder="Referans Tablo Model">
                            </div>

                            <div class="form-group col-12 col-lg-6">
                                <label class="required fw-semibold fs-6 mb-2">Görüntülenecek Alan</label>
                                <select name="show_column" data-control="select2" data-placeholder="Görüntülenecek Alan" data-allow-clear="true" class="form-control form-control-solid ">
                                    <option value="">Seçiniz</option>
                                </select>
                            </div>

                            <div class="form-group col-12 col-lg-6">
                                <label class="required fw-semibold fs-6 mb-2">Eşleşecek Alan</label>
                                <select name="match_column" data-control="select2" data-placeholder="Eşleşecek Alan" data-allow-clear="true" class="form-control form-control-solid ">
                                    <option value="">Seçiniz</option>
                                </select>
                            </div>

                        </div>

                        <div class="text-center border-top pt-10 mt-5">
                            <button type="reset" class="btn btn-light me-3" data-dismiss="modal"> Vazgeç </button>
                            <button type="submit" class="btn btn-primary"
                                    data-kt-users-modal-action="submit"> Kaydet
                            </button>
                            @include('crudPackage::components.loading')
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade modal-xl" data-bs-backdrop="static" data-bs-keyboard="false" id="repeaterModal"
         aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered ">
            <div class="modal-content">
                <div class="modal-header" id="repeaterModalHeader">
                    <h2 class="fw-bold">Tekrarlanan Alan Ekle</h2>
                    <div class="btn btn-icon btn-sm btn-active-icon-primary" data-dismiss="modal">
                        <i class="ki-outline ki-cross fs-1"></i>
                    </div>
                </div>

                <div class="modal-body scroll-y mx-5 mx-xl-10">
                    <form id="repeaterForm" class="form"
                          method="post" action="{{route('cruds.repeater.store',$value->id)}}">
                        @method('PUT')
                        <div class="row scroll-y me-n7 pe-7" id="repeaterModalScroll"
                             data-kt-scroll="true" data-kt-scroll-activate="{default: false, lg: true}"
                             data-kt-scroll-max-height="auto"
                             data-kt-scroll-dependencies="#repeaterModalHeader"
                             data-kt-scroll-wrappers="#repeaterModalScroll"
                             data-kt-scroll-offset="300px" style="max-height: 281px;">

                            <div class="form-group col-12">
                                <label class="required fw-semibold fs-6 mb-2">Referans Alınacak Alan</label>
                                <select name="repeater_column_name" data-control="select2" data-placeholder="Referans Alınacak Alan Seçiniz" data-allow-clear="true" class="form-control form-control-solid ">
                                    <option value="">Seçiniz</option>
                                    @foreach($columns as $column)
                                        <option value="{{$column->name}}">{{$column->name}}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div data-repeater-list="repeaterArea">
                                <div data-item-no="0" data-repeater-item>
                                    <div class="form-group row">

                                        <div class="col-md-1 form-group handle"><i class="bi text-dark me-3 fs-4 bi-arrows-move"></i></div>
                                        <div class="col-md-6 form-group">
                                            <label class="fw-semibold fs-6 mb-2">Alan Bilgileri</label>
                                            <textarea id="area_info" name="area_info" data-modal-json="true" class="form-control form-control-solid" data-value='{"column_name":"geçici alan adı","validation":"required","title":"Form element Başlık","class":"Form element Class İsimleri Örn : (col-md-6)","relationships":{"table":"db tablo adı","key":"referans gösterilecek alan adı","show":"Görüntülenecek alan adı"},"option":{"key":"value"},"search":"true ise select2 fonksiyonunun olması demektir","rows":"textarea satır sayısı"}'></textarea>
                                            <div id="area_info_json_organizer" class="btn jsonOrganizerButton btn-secondary mt-3">JSON'u Düzenle</div>
                                        </div>

                                        <div class="col-md-4 form-group">
                                            <label class="fw-semibold fs-6 mb-2">Tip</label>
                                            <select name="form_type_id" class="form-control form-control-solid">
                                                <option value="">Seçiniz</option>
                                                @foreach($formTypes as $type)
                                                    <option value="{{$type->id}}">{{$type->title}}</option>
                                                @endforeach
                                            </select>
                                        </div>

                                        <div class="col-md-1 form-group">
                                            <a href="javascript:;" data-repeater-delete class="btn btn-flex btn-tertiary mt-6">
                                                <i class="ki-outline ki-trash  fs-3"></i>
                                                Sil
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="form-group mt-5">
                                <a href="javascript:;" data-repeater-create class="btn btn-primary">
                                    <i class="ki-duotone ki-plus fs-3"></i>
                                    Satır Ekle
                                </a>
                            </div>
                        </div>

                        <div class="text-center border-top pt-10 mt-5">
                            <button type="reset" class="btn btn-light me-3" data-dismiss="modal"> Vazgeç </button>
                            <button type="submit" class="btn btn-primary"
                                    data-kt-users-modal-action="submit"> Kaydet
                            </button>
                            @include('crudPackage::components.loading')
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
@section('style')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.5/codemirror.min.css">
@endsection
@section('js')
    <script src="{{asset('crud/vendor/formRepeater/formRepeater.bundle.js')}}"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.5/codemirror.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.5/mode/javascript/javascript.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.5/addon/format/formatting.min.js"></script>

    <script>
        function relationshipPivotTable(self)
        {
            let select = $(self);
            let value  = select.val();

            if (value == 'belongsToMany')
            {
                $('#relationship_column_name').addClass('d-none');
                $('#relationship_pivot_table').removeClass('d-none');
            }
            else
            {
                $('#relationship_pivot_table').addClass('d-none');
                $('#relationship_column_name').removeClass('d-none');
            }
        }

        function getColumns(self,showColumn = null, matchColumn = null )
        {
            let showColumnSelector  = $('[name="show_column"]');
            let matchColumnSelector = $('[name="match_column"]');
            let options             = '<option>Yükleniyor...</option>';

            showColumnSelector.html(options);
            matchColumnSelector.html(options);

            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });

            $.ajax({
                type: "POST",
                dataType: "json",
                url: '/cruds/getColumns',
                data: {
                    table_name: $(self).val(),
                },
                success: function (response)
                {
                    if (response.result == 1)
                    {
                        showColumnOption = '<option>Seçiniz</option>';
                        matchColumnOption = '<option>Seçiniz</option>';

                        $(response.response).each(function(index,element)
                        {
                            showColumnOption  += '<option ' + (showColumn == element.name  ? "selected" : null )   + ' value="'+ element.name +'">'+ element.name +'</option>';
                            matchColumnOption += '<option ' + (matchColumn == element.name  ? "selected" : null )   + ' value="'+ element.name +'">'+ element.name +'</option>';
                        });

                        showColumnSelector.html(showColumnOption);
                        matchColumnSelector.html(matchColumnOption);
                    }
                },
                error : function (response)
                {
                    if (response.responseJSON.message)
                    {
                        messageAlert(0,response.responseJSON.message);
                    }
                    else
                    {
                        messageAlert(0,'İşlem Başarısız. Lütfen daha sonra tekrar deneyiniz.');
                    }
                }
            });
        }
    </script>
@endsection