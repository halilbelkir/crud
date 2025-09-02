@extends('crudPackage::layout.main',['activePage' => 'Yetkiler'])
@section('content')
    <div class="card">
        <div class="card-header border-0 pt-6">
            <div class="card-title">
                <div class="d-flex align-items-center position-relative my-1">
                    <i class="ki-outline ki-magnifier fs-3 position-absolute ms-5"></i>
                    <input type="text" data-kt-data-table-filter="search"
                           class="form-control form-control-solid w-250px ps-13" placeholder="Arama Yap">
                </div>
            </div>
            <div class="card-toolbar">
                <div class="d-flex justify-content-end" id="exportButtons" data-kt-user-table-toolbar="base">
                    <button type="button" class="btn btn-light-primary me-3" data-kt-menu-trigger="click"
                            data-kt-menu-placement="bottom-end">
                        <i class="ki-outline ki-exit-up fs-2"></i> Dışa Aktar
                    </button>
                    <div class="menu menu-sub menu-sub-dropdown menu-column menu-rounded menu-gray-600 menu-state-bg-light-primary fw-semibold fs-7 w-200px w-md-200px py-4"
                         data-kt-menu="true" data-popper-placement="bottom-end">
                        <div class="menu-item px-3">
                            <a href="#" class="menu-link px-3" data-table-export-button-name="excel"> Excel </a>
                        </div>
                        <div class="menu-item px-3">
                            <a href="#" class="menu-link px-3" data-table-export-button-name="pdf"> Pdf </a>
                        </div>
                    </div>

                    @if(auth()->user()->hasPermission('role-groups.store'))
                        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#insertModal">
                            <i class="ki-duotone ki-plus fs-2"></i> Ekle
                        </button>
                    @endif

                </div>
                <div class="modal fade modal-xl" data-bs-backdrop="static" data-bs-keyboard="false" id="insertModal"
                     aria-hidden="true" style="display: none;">
                    <div class="modal-dialog modal-dialog-centered ">
                        <div class="modal-content">
                            <div class="modal-header" id="kt_modal_add_user_header">
                                <h2 class="fw-bold">Rol Ekle</h2>
                                <div class="btn btn-icon btn-sm btn-active-icon-primary" data-dismiss="modal">
                                    <i class="ki-outline ki-cross fs-1"></i>
                                </div>
                            </div>

                            <div class="modal-body scroll-y mx-5 mx-xl-10">
                                <form id="addUpdateForm" class="form fv-plugins-bootstrap5 fv-plugins-framework"
                                      method="post" action="{{route('role-groups.store')}}">
                                    <div class="row scroll-y me-n7 pe-7" id="kt_modal_add_user_scroll"
                                         data-kt-scroll="true" data-kt-scroll-activate="{default: false, lg: true}"
                                         data-kt-scroll-max-height="auto"
                                         data-kt-scroll-dependencies="#kt_modal_add_user_header"
                                         data-kt-scroll-wrappers="#kt_modal_add_user_scroll"
                                         data-kt-scroll-offset="300px" style="max-height: 281px;">

                                        <div class="form-group col-12 mb-7 fv-plugins-icon-container">
                                            <label class="required fw-semibold fs-6 mb-2">Başlık</label>
                                            <input type="text" name="title"
                                                   class="form-control form-control-solid mb-3 mb-lg-0"
                                                   placeholder="Başlık">
                                        </div>

                                        <div class="row form-group p-0 m-0">
                                            <label class="fw-bold fs-4">İzinler</label>
                                            <div class="validation" style="margin-bottom: -10px;"></div>
                                            <div class="col-12 separator border-2 my-5"></div>
                                            @foreach($cruds as $crud)
                                                <div class="col-12 col-lg-6 col-xl-4 pb-4">
                                                    <label class="fw-bold fs-6 mb-5">{{$crud->title}}</label>
                                                    <div class="form-check form-check-sm mb-3">
                                                        <input class="form-check-input" onchange="allCheck(this)" name="permissions_all" id="role_{{$crud->id}}_all" data-checkbox-class="role_{{$crud->id}}" type="checkbox" value="1"/>
                                                        <label class="form-check-label text-gray-700 fw-semibold" for="role_{{$crud->id}}_all">
                                                            Hepsini Seç
                                                        </label>
                                                    </div>
                                                    @foreach($permissions as $permissionKey => $permission)
                                                        <div class="form-check form-check-sm mb-3">
                                                            <input class="form-check-input role_{{$crud->id}}" id="role_{{$permissionKey}}" name="permissions[{{$crud->id}}][{{$permissionKey}}]" type="checkbox" value="1"/>
                                                            <label class="form-check-label text-gray-700 fw-semibold" for="role_{{$permissionKey}}">
                                                                {{$permission['title']}}
                                                            </label>
                                                        </div>
                                                    @endforeach
                                                </div>
                                            @endforeach
                                        </div>

                                    </div>

                                    <div class="text-center border-top pt-10 mt-5">
                                        <button type="reset" class="btn btn-light me-3" data-dismiss="modal"> Vazgeç </button>
                                        <button type="submit" class="btn btn-primary buttonForm"> Kaydet </button>
                                        @include('crudPackage::components.loading')
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
        <div class="card-body py-4">
            <div id="kt_table_users_wrapper" class="dataTables_wrapper dt-bootstrap4 no-footer">
                <div class="table-responsive">
                    <table class="table align-middle table-row-dashed table-hover fs-6 gy-4 gs-7 dataTable no-footer" id="data-tables">
                        <thead>
                        <tr class="text-start text-muted fw-bold fs-7 text-uppercase gs-0">
                            <th class="min-w-125px">Başlık</th>
                            <th class="min-w-125px"></th>
                        </tr>
                        </thead>
                        <tbody class="fw-semibold text-gray-600"></tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection
@section('datatables.ajax.url'){{route('role-groups.datatables')}}@stop
@section('datatables.files.title') Yetkiler @stop
@section('datatables.files.columns') [0,1] @stop
@section('datatables.columns')
    [
        { data: 'title', name: 'title' },
        { data: 'actions', name: 'actions','className':'text-end'},
    ]
@stop