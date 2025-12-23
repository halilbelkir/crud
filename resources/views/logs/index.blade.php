@extends('crudPackage::layout.main',['activePage' => 'Geçmiş'])
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
                </div>
            </div>
        </div>
        <div class="card-body py-4">
            <div id="kt_table_users_wrapper" class="dataTables_wrapper dt-bootstrap4 no-footer">
                <div class="table-responsive">
                    <table class="table align-middle table-row-dashed table-hover fs-6 gy-4 gs-7 dataTable no-footer" id="data-tables">
                        <thead>
                        <tr class="text-start text-muted fw-bold fs-7 text-uppercase gs-0">
                            <th class="min-w-125px">Tablo</th>
                            <th class="min-w-125px">Tablo ID</th>
                            <th class="min-w-125px">İşlem</th>
                            <th class="min-w-125px">İşlemi Yapan</th>
                            <th class="min-w-125px">İşlem Zamanı</th>
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

@section('datatables.ajax.url'){{route('logs.datatables')}}@stop
@section('datatables.files.title') Geçmiş @stop
@section('datatables.files.columns') [0,1] @stop
@section('datatables.columns')
    [
        { data: 'log_name', name: 'log_name' },
        { data: 'subject_id', name: 'subject_id' },
        { data: 'description', name: 'description' },
        { data: 'user', name: 'user' },
        { data: 'created_at', name: 'created_at' },
        { data: 'actions', name: 'actions','className':'text-end'},
    ]
@stop