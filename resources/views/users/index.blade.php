@extends('crudPackage::layout.main',['activePage' => 'Kullanıcılar'])
@section('content')
    <div class="card">
        <div class="card-header border-0 pt-6">
            <div class="card-title">
                <div class="d-flex align-items-center position-relative my-1">
                    <i class="ki-duotone ki-magnifier fs-3 position-absolute ms-5">
                        <span class="path1"></span>
                        <span class="path2"></span>
                    </i>
                    <input type="text" data-kt-data-table-filter="search"
                           class="form-control form-control-solid w-250px ps-13" placeholder="Arama Yap">
                </div>
            </div>
            <div class="card-toolbar">
                <div class="d-flex justify-content-end" id="exportButtons" data-kt-user-table-toolbar="base">
                    <button type="button" class="btn btn-secondary me-3" data-kt-menu-trigger="click"
                            data-kt-menu-placement="bottom-end">
                        <i class="ki-duotone ki-exit-up fs-2">
                            <span class="path1"></span>
                            <span class="path2"></span>
                        </i> Dışa Aktar
                    </button>
                    <div class="menu menu-sub menu-sub-dropdown menu-column menu-rounded menu-gray-600 menu-state-bg-primary fw-semibold fs-7 w-200px w-md-200px py-4"
                         data-kt-menu="true" data-popper-placement="bottom-end">
                        <div class="menu-item px-3">
                            <a href="#" class="menu-link px-3" data-table-export-button-name="excel"> Excel </a>
                        </div>
                        <div class="menu-item px-3">
                            <a href="#" class="menu-link px-3" data-table-export-button-name="pdf"> Pdf </a>
                        </div>
                    </div>

                    @if(auth()->user()->hasPermission('users.store'))
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
                                <h2 class="fw-bold">Kullanıcı Ekle</h2>
                                <div class="btn btn-icon btn-sm btn-active-icon-primary" data-dismiss="modal">
                                    <i class="ki-duotone ki-cross fs-1"><span class="path1"></span><span
                                                class="path2"></span></i>
                                </div>
                            </div>

                            <div class="modal-body scroll-y mx-5 mx-xl-10">
                                <div class="col-12">
                                    <div class="alert alert-dismissible bg-primary d-flex flex-column align-items-center flex-sm-row p-5 mb-10">
                                        <i class="ki-outline ki-information fs-2hx text-light me-4 mb-sm-0"></i>
                                        <div class="d-flex flex-column text-light pe-0 pe-sm-10">
                                            <h4 class="mb-2 fw-bolder text-light">Bilgilendirme</h4>
                                            <div class="fw-bold">Yeni oluşturulan her kullanıcının şifresi <span
                                                        class="fw-bolder">{{ Str::camel(Str::slug(settings('title'))) }}12345.,</span> olarak otomatik
                                                tanımlanmaktadır.
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <form id="addUpdateForm" class="form  fv-plugins-bootstrap5 fv-plugins-framework"
                                      method="post" action="{{route('users.store')}}">
                                    <div class="row scroll-y me-n7 pe-7" id="kt_modal_add_user_scroll"
                                         data-kt-scroll="true" data-kt-scroll-activate="{default: false, lg: true}"
                                         data-kt-scroll-max-height="auto"
                                         data-kt-scroll-dependencies="#kt_modal_add_user_header"
                                         data-kt-scroll-wrappers="#kt_modal_add_user_scroll"
                                         data-kt-scroll-offset="300px" style="max-height: 281px;">
                                        <div class="form-group col-12 col-lg-6 mb-7 fv-plugins-icon-container">
                                            <label class="required fw-semibold fs-6 mb-2">Ad & Soyad</label>
                                            <input type="text" name="name"
                                                   class="form-control form-control-solid mb-3 mb-lg-0"
                                                   placeholder="Ad & Soyad">
                                        </div>

                                        <div class="form-group col-12 col-lg-6 mb-7 fv-plugins-icon-container">
                                            <label class="required fw-semibold fs-6 mb-2">E-Mail</label>
                                            <input type="email" name="email"
                                                   class="form-control form-control-solid mb-3 mb-lg-0"
                                                   placeholder="E-Mail">
                                        </div>

                                        <div class="form-group col-12 mb-7 fv-plugins-icon-container">
                                            <label class="fw-semibold fs-6 mb-2">Yetkisi</label>
                                            <select name="role_group_id" class="form-control form-control-solid mb-3 mb-lg-0">
                                                <option value="">Seçiniz</option>
                                                @foreach($roles as $role)
                                                    <option value="{{$role->id}}">{{$role->title}}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>

                                    <div class="text-center border-top pt-10 mt-5">
                                        <button type="reset" class="btn btn-light me-3" data-dismiss="modal"> Vazgeç
                                        </button>
                                        <button type="submit" class="btn btn-primary buttonForm"
                                                data-kt-users-modal-action="submit"> Kaydet
                                        </button>
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
                            <th class="min-w-125px">Ad & Soyad</th>
                            <th class="min-w-125px">Yetkisi</th>
                            <th class="min-w-125px">Durum</th>
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

@section('datatables.ajax.url'){{route('users.datatables')}}@stop
@section('datatables.files.title') Kullanıcılar @stop
@section('datatables.files.columns') [0,1,2] @stop
@section('datatables.columns')
    [
        { data: 'name', name: 'name' },
        { data: 'role_group', name: 'role_group' },
        { data: 'status', name: 'status' },
        { data: 'actions', name: 'actions','className':'text-end'},
    ]
@stop