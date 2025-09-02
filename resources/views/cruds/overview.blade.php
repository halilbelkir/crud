<div class="tab-pane fade show active" id="overview" role="tabpanel">

    <div class="pb-5 fs-6">
        <div class="text-decoration-underline fw-bold mt-5">Başlık :</div>
        <div class="text-gray-600">{{$value->title ?? null}}</div>
        <div class="separator separator-dashed my-3"></div>

        <div class="text-decoration-underline fw-bold mt-5">Link :</div>
        <div class="text-gray-600">{{$value->slug ?? null}}</div>
        <div class="separator separator-dashed my-3"></div>

        <div class="text-decoration-underline fw-bold mt-5">Tekli Sayfa Başlık :</div>
        <div class="text-gray-600">{{$value->display_single ?? null}}</div>
        <div class="separator separator-dashed my-3"></div>

        <div class="text-decoration-underline fw-bold mt-5">Çoklu Sayfa Başlık :</div>
        <div class="text-gray-600">{{$value->display_plural ?? null}}</div>
        <div class="separator separator-dashed my-3"></div>

        <div class="text-decoration-underline fw-bold mt-5">İkon :</div>
        <div class="text-gray-600">{!! $value->icon ?? null !!} {{$value->icon ?? null}}</div>
        <div class="separator separator-dashed my-3"></div>

        <div class="text-decoration-underline fw-bold mt-5">Model Dizini :</div>
        <div class="text-gray-600">{{$value->model ?? null}}</div>
    </div>
</div>