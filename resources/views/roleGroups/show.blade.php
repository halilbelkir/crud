@extends('crudPackage::layout.main',['activePage' => $value->title.' Detay','parentPage' => 'Yetkiler','parentPageRoute' => route('role-groups.index')])
@section('content')
    @php
        $statusClass = $value->status == 0 ? 'warning' : 'success';
    @endphp
    <div class="card mb-5 mb-xl-10">
        <div class="card-body pt-9 pb-0">
            <div class="d-flex flex-wrap flex-sm-nowrap">
                <div class="me-7 mb-4">
                    <div class="symbol symbol-100px symbol-lg-160px symbol-fixed position-relative">
                        <img src="{{asset('crud/images/avatar.png')}}" alt="avatar">
                    </div>
                </div>
                <div class="flex-grow-1">
                    <div class="d-flex justify-content-between align-items-start flex-wrap mb-2">
                        <div class="d-flex flex-column">
                            <div class="d-flex align-items-center mb-2">
                                <a href="#" class="text-gray-900 text-hover-primary fs-2 fw-bold me-1">{{$value->title}}</a>
                            </div>
                        </div>
                        @if(auth()->user()->hasPermission('role-groups.edit') || auth()->user()->hasPermission('role-groups.destroy'))
                            <div class="d-flex my-4">
                                <div class="me-0">
                                    <button class="btn btn-sm btn-icon btn-bg-light btn-active-color-primary" data-kt-menu-trigger="click" data-kt-menu-placement="bottom-end">
                                        <i class="ki-solid ki-dots-horizontal fs-2x"></i>
                                    </button>
                                    <div class="menu menu-sub menu-sub-dropdown menu-column menu-rounded menu-gray-800 menu-state-bg-primary fw-semibold w-200px py-3" data-kt-menu="true">

                                        @if(auth()->user()->hasPermission('role-groups.edit'))
                                            <div class="menu-item px-3">
                                                <a href="{{route('role-groups.edit',$value->id)}}" class="menu-link flex-stack px-3">
                                                    Düzenle
                                                </a>
                                            </div>
                                        @endif

                                        @if(auth()->user()->hasPermission('role-groups.destroy'))
                                            <div class="menu-item px-3">
                                                <a data-route="{{route('role-groups.destroy',$value->id)}}" onclick="destroy(this)" data-title="{{$value->title.' isimli rolü'}}"  class="menu-link px-3">
                                                    Sil
                                                </a>
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        @endif
                    </div>

                    <div class="d-flex flex-wrap flex-stack">
                        <div class="d-flex flex-column flex-grow-1 pe-8">
                            <div class="d-flex flex-wrap">
                                <div class="border border-gray-300 border-dashed rounded min-w-125px py-3 px-4 me-6 mb-3">
                                    <div class="d-flex align-items-center">
                                        <div class="fs-2 fw-bold counted" data-kt-countup="true" data-kt-countup-value="4500" data-kt-countup-prefix="$" data-kt-initialized="1">
                                            {{$value->users->count()}}
                                        </div>
                                    </div>
                                    <div class="fw-semibold fs-6 text-gray-500">Aktif Kullanıcı</div>
                                </div>
                            </div>
                        </div>
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
            <div class="tab-content" id="myTabContent">
                @include('crudPackage::roleGroups.overview')
            </div>
        </div>
    </div>
@endsection
@section('js')
    <script>
        permissionCheckboxCheck();
    </script>
@endsection