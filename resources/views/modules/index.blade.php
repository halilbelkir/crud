@extends('crudPackage::layout.main',['activePage' => $crud->display_plural])
@section('content')
    @if($crud->content) {!! $crud->content !!} @endif
    <div class="card">
        <div class="card-header border-0 pt-6">
            <div class="card-title">
                <div class="d-flex align-items-center position-relative my-1">
                    <i class="ki-outline ki-magnifier fs-3 position-absolute ms-5"></i>
                    <input type="text" data-kt-data-table-filter="search" class="form-control form-control-solid w-250px ps-13" placeholder="Arama Yap">
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

                    @if(auth()->user()->hasPermission( $crud->slug . '.create'))
                        <a href="{{route($crud->slug . '.create')}}" class="btn btn-primary">
                            <i class="ki-duotone ki-plus fs-2"></i> Ekle
                        </a>
                    @endif

                </div>
            </div>
        </div>
        <div class="card-body py-4">
            <div id="kt_table_users_wrapper" class="dataTables_wrapper dt-bootstrap4 no-footer">
                <div class="table-responsive">
                    <table class="table align-middle @if(isset($area1) && $area1->order_column_name) moduleSortable @endif  table-row-dashed fs-6 gy-4 dataTable no-footer"
                           id="data-tables"
                           @if(isset($area1) && $area1->order_column_name) data-link="{{route($crud->slug . '.orderable')}}" @endif
                           @if(isset($area1) && $area1->order_column_name) data-order-column="{{$area1->order_column_name}}" @endif
                           @if(isset($area1) && $area1->order_column_name) data-order-direction="{{$area1->order_direction}}" @endif
                    >
                        <thead>
                            <tr class="text-start text-muted fw-bold fs-7 text-uppercase gs-0">
                                @if(isset($area1) && $area1->order_column_name) <th></th> @endif
                                @foreach($crud->browseColumns as $columnKey => $column)
                                    @php
                                        if(isset($area1) && $area1->order_column_name)
                                        {
                                            $columnKey += 1;
                                        }

                                        $datatableColumns[$columnKey]['data']  = $column->column_name;
                                        $datatableColumns[$columnKey]['title'] = $column->title;
                                    @endphp

                                    <th>{{$column->title}}</th>

                                @endforeach
                                <th></th>
                            </tr>
                        </thead>
                        <tbody class="fw-semibold text-gray-600"></tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

@endsection
@section('datatables.ajax.url'){{route( $crud->slug .'.datatables')}}@stop
@section('datatables.files.title') {{$crud->display_plural}} @stop
@section('datatables.files.columns') {!! json_encode(array_keys($datatableColumns)) !!} @stop
@section('datatables.columns')
    @php
        $lastKey = array_key_last($datatableColumns) + 1;

        $datatableColumns[$lastKey]['data']      = 'actions';
        $datatableColumns[$lastKey]['name']      = 'actions';
        $datatableColumns[$lastKey]['className'] = 'text-end';

        if(isset($area1) && $area1->order_column_name)
        {
            $datatableColumns[0]['data'] = 'orderable';
            $datatableColumns[0]['name'] = 'orderable';
            ksort($datatableColumns);
        }
    @endphp

    {!! json_encode($datatableColumns) !!}
@stop