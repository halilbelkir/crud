@extends('crudPackage::layout.main',['activePage' => ' Ayarlar'])
@section('content')
    <div class="card">
        <div class="card-body py-4">
            <form id="addUpdateForm" class="form row justify-content-center fv-plugins-bootstrap5 fv-plugins-framework"
                  method="post" action="{{route('settings.update',$value->id)}}">
                @method('PUT')
                <div class="form-group col-12 col-lg-6 mb-7 fv-plugins-icon-container">
                    <label class="required fw-semibold fs-6 mb-2">Başlık</label>
                    <i data-bs-toggle="tooltip" data-bs-placement="top" title='Giriş sayfası ve title meta etiketi etkilenir.' data-bs-custom-class="tooltip-inverse" class="ki-outline color-primary fs-4 ki-information-5"></i>
                    <input type="text" value="{{$value->title ?? null}}" name="title"
                           class="form-control form-control-solid mb-3 mb-lg-0" placeholder="Başlık">
                </div>

                <div class="form-group col-12 col-lg-6 mb-7 fv-plugins-icon-container">
                    <label class="required fw-semibold fs-6 mb-2">Karşılama Mesajı</label>
                    <input type="text" value="{{$value->subtitle ?? null}}" name="subtitle"
                           class="form-control form-control-solid mb-3 mb-lg-0" placeholder="Karşılama Mesajı">
                </div>

                <div class="form-group col-12 mb-7 fv-plugins-icon-container">
                    <label class="fw-semibold fs-6 mb-2">Dil Seçiniz</label>
                    <select data-control="select2"  data-placeholder="Dil Seçiniz" data-allow-clear="true"
                        class="form-control form-control-solid"
                        data-select-multiple="true"
                        multiple="multiple"
                        name="languages[]"
                    >
                        <option value="">Dil Seçiniz</option>
                        @foreach($languages as $language)
                            <option {{ in_array((string)$language->id, $value->languages ?? []) ? 'selected' : '' }} value="{{ $language->id }}">{{ $language->title }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="form-group col-12 col-lg-6 mb-7 fv-plugins-icon-container">
                    <label class="required fw-semibold fs-6 mb-2">Logo</label>
                    @if(empty($value->logo))
                        <input type="file"  name="logo" class="form-control form-control-solid mb-3 mb-lg-0" placeholder="Logo">
                    @else
                        <div class="image-input image-input-outline showImage">
                            <label class="btn btn-icon btn-circle btn-active-color-primary w-25px h-25px bg-body shadow" style="top: 0;left: 50%;" data-kt-image-input-action="change" data-bs-toggle="tooltip" data-bs-trigger="hover" title="Resmi Değiştir">
                                <i class="ki-outline ki-pencil fs-7"></i>
                                <input type="file" class="imageUpdate" value="{{ $value->logo }}" name="logo">
                            </label>
                            <img src="{{$value->logo}}" class="mb-7 w-100 object-fit-contain imageUpdatePreview h-175px">
                        </div>
                    @endif
                </div>

                <div class="form-group col-12 col-lg-6 mb-7 fv-plugins-icon-container">
                    <label class="required fw-semibold fs-6 mb-2">İkon</label>
                    @if(empty($value->icon))
                        <input type="file"  name="icon" class="form-control form-control-solid mb-3 mb-lg-0" placeholder="İkon">
                    @else
                        <div class="image-input image-input-outline showImage">
                            <label class="btn btn-icon btn-circle btn-active-color-primary w-25px h-25px bg-body shadow" style="top: 0;left: 50%;" data-kt-image-input-action="change" data-bs-toggle="tooltip" data-bs-trigger="hover" title="Resmi Değiştir">
                                <i class="ki-outline ki-pencil fs-7"></i>
                                <input type="file" class="imageUpdate" value="{{ $value->icon }}" name="icon">
                            </label>
                            <img src="{{$value->icon}}" class="mb-7 w-100 object-fit-contain imageUpdatePreview h-175px">
                        </div>
                    @endif
                </div>

                <div class="form-group col-12 col-lg-6 mb-7 fv-plugins-icon-container">
                    <label class="required fw-semibold fs-6 mb-2">Loader Gif</label>
                    @if(empty($value->loader))
                        <input type="file"  name="loader" class="form-control form-control-solid mb-3 mb-lg-0" placeholder="İkon">
                    @else
                        <div class="image-input image-input-outline showImage">
                            <label class="btn btn-icon btn-circle btn-active-color-primary w-25px h-25px bg-body shadow" style="top: 0;left: 50%;" data-kt-image-input-action="change" data-bs-toggle="tooltip" data-bs-trigger="hover" title="Resmi Değiştir">
                                <i class="ki-outline ki-pencil fs-7"></i>
                                <input type="file" class="imageUpdate" value="{{ $value->loader }}" name="loader">
                            </label>
                            <img src="{{$value->loader}}" class="mb-7 w-100 object-fit-contain imageUpdatePreview h-175px">
                        </div>
                    @endif
                </div>

                <div class="form-group col-12 col-lg-6 mb-7 fv-plugins-icon-container">
                    <label class="required fw-semibold fs-6 mb-2">Arka Plan Resmi </label>
                    <i data-bs-toggle="tooltip" data-bs-placement="top" title='Giriş sayfasın ya da vb. sayfalarında bulunan arka plan resmidir.' data-bs-custom-class="tooltip-inverse" class="ki-outline color-primary fs-4 ki-information-5"></i>
                    @if(empty($value->bg_image))
                        <input type="file"  name="bg_image" class="form-control form-control-solid mb-3 mb-lg-0" placeholder="İkon">
                    @else
                        <div class="image-input image-input-outline showImage">
                            <label class="btn btn-icon btn-circle btn-active-color-primary w-25px h-25px bg-body shadow" style="top: 0;left: 50%;" data-kt-image-input-action="change" data-bs-toggle="tooltip" data-bs-trigger="hover" title="Resmi Değiştir">
                                <i class="ki-outline ki-pencil fs-7"></i>
                                <input type="file" class="imageUpdate" value="{{ $value->bg_image }}" name="bg_image">
                            </label>
                            <img src="{{$value->bg_image}}" class="mb-7 w-100 object-fit-contain imageUpdatePreview h-175px">
                        </div>
                    @endif
                </div>

                <div class="form-group col-12 col-lg-6 mb-7 fv-plugins-icon-container">
                    <label class="required fw-semibold fs-6 mb-2">Panel Renk 1</label>
                    <input type="color" value="{{$value->color_1 ?? null}}" name="color_1"
                           class="form-control form-control-solid mb-3 mb-lg-0" placeholder="Panel Renk 1">
                </div>

                <div class="form-group col-12 col-lg-6 mb-7 fv-plugins-icon-container">
                    <label class="required fw-semibold fs-6 mb-2">Panel Renk 2</label>
                    <input type="color" value="{{$value->color_2 ?? null}}" name="color_2"
                           class="form-control form-control-solid mb-3 mb-lg-0" placeholder="Panel Renk 2">
                </div>

                <div class="clear"></div>

                <div class="pt-5 col-lg-4 col-xl-2 text-center">
                    <button type="submit" class="btn btn-primary buttonForm w-100"> Kaydet</button>
                    @include('crudPackage::components.loading')
                </div>
            </form>
        </div>
    </div>
@endsection
@section('js')
    <script>
        $('[data-select-multiple="true"]').each(function() {
            const el = $(this);

            el.select2({
                minimumResultsForSearch: 0,
                closeOnSelect: false,
                allowClear: true,
            }).on('select2:select', function (e)
            {
                let max      = 4;
                let selected = $(this).select2('data').length;

                if (selected > max)
                {
                    let id     = e.params.data.id;
                    let values = $(this).val().filter(v => v !== id);

                    $(this).val(values).trigger('change');

                    let message = 'En fazla ' + max + ' seçim yapabilirsiniz.';
                    messageAlert(2,message);
                }
            });
        });
    </script>
@endsection