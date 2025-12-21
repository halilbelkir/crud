@extends('crudPackage::layout.main',['activePage' => $value->title.' Detay','parentPage' => 'Yetkiler','parentPageRoute' => route('menus.index')])
@section('content')
    @php
        $statusClass = $value->status == 0 ? 'warning' : 'success';
    @endphp
    <div class="card mb-5 mb-xl-10">
        <div class="card-body pt-9 pb-0">
            <div class="d-flex flex-wrap flex-sm-nowrap">
                <div class="flex-grow-1">
                    <div class="d-flex justify-content-between align-items-start flex-wrap mb-2">
                        <div class="d-flex flex-column">
                            <div class="d-flex align-items-center mb-2">
                                <a href="#" class="text-gray-900 text-hover-primary fs-2 fw-bold me-1">{{$value->title}}</a>
                            </div>
                        </div>
                        @if(auth()->user()->hasPermission('menus.edit') || auth()->user()->hasPermission('menus.destroy'))
                            <div class="d-flex my-4">
                                <div class="me-0">
                                    <button class="btn btn-sm btn-icon btn-bg-light btn-active-color-primary" data-kt-menu-trigger="click" data-kt-menu-placement="bottom-end">
                                        <i class="ki-solid ki-dots-horizontal fs-2x"></i>
                                    </button>
                                    <div class="menu menu-sub menu-sub-dropdown menu-column menu-rounded menu-gray-800 menu-state-bg-primary fw-semibold w-200px py-3" data-kt-menu="true">

                                        @if(auth()->user()->hasPermission('menus.edit'))
                                            <div class="menu-item px-3">
                                                <a href="{{route('menus.edit',$value->id)}}" class="menu-link flex-stack px-3">
                                                    Düzenle
                                                </a>
                                            </div>

                                            @if($value->id != 1)
                                                <div class="menu-item px-3">
                                                    <a data-status="{{$value->status}}" onclick="statusUpdate(this)" data-model-name="Menu" data-id="{{$value->id}}" data-route="{{route('statusUpdate')}}" class="menu-link px-3">
                                                        {{$value->status == 1 ? 'Pasif Et' : 'Aktif Et'}}
                                                    </a>
                                                </div>
                                            @endif
                                        @endif

                                        @if(auth()->user()->hasPermission('menus.destroy')  && $value->id != 1)
                                            <div class="menu-item px-3">
                                                <a data-route="{{route('menus.destroy',$value->id)}}" onclick="destroy(this)" data-title="{{$value->title.' isimli menüyü'}}"  class="menu-link px-3">
                                                    Sil
                                                </a>
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <ul class="nav nav-stretch nav-line-tabs nav-line-tabs-2x border-transparent fs-5 fw-bold">
                <li class="nav-item mt-2">
                    <a class="nav-link text-active-primary ms-0 me-10 py-5 active"  data-bs-toggle="tab" href="#overview">
                        Genel Bilgiler
                    </a>
                </li>
            </ul>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <div class="tab-content position-relative" id="myTabContent">
                @include('crudPackage::menus.overview')
            </div>
        </div>
    </div>
@endsection
@section('js')
    <script src="https://johnny.github.io/jquery-sortable/js/jquery-sortable.js"></script>
    <script>
        $(function  ()
        {
            let selector = "#menu";

            $(selector).sortable({
                onDrop: function (item, container, _super)
                {
                    if(item.closest('.children').length > 0)
                    {
                        item.removeClass('parent');
                        item.children().first().remove();
                    }
                    else if(item.closest('.children').length == 0 && item.find('ul').length == 0)
                    {
                        item.addClass('parent');
                        item.append('<ul class="children"></ul>');
                    }

                    let menuData = getMenuStructure(selector + " .parent");

                    $.ajaxSetup({
                        headers: {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                        }
                    });

                    $.ajax({
                        type: "POST",
                        dataType: "json",
                        url: '/menus/orderable',
                        data: {
                            menus: menuData
                        },
                        success: function (response)
                        {
                            messageToast(selector,1,response.message)
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

                    _super(item, container);
                },
            });

            function getMenuStructure(selector)
            {
                var menuData = [];

                $(selector).each(function() {
                    var item = {
                        id: $(this).attr('data-id'),
                        children: []
                    };

                    $(this).find('.children li').each(function()
                    {
                        item.children.push({
                            id: $(this).attr('data-id'),
                        });
                    });

                    menuData.push(item);
                });

                return menuData;
            }
        });
    </script>
@endsection