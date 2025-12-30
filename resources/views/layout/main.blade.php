<!DOCTYPE html>
<html lang="tr">
<head>
    <title>{{isset($activePage) ? $activePage.' - ' : null}} {{ settings('title') }}</title>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="{{ settings('subtitle') }}">
    <link rel="shortcut icon" href="{{ asset(settings('icon')) }}" type="image/png">
    <meta name="msapplication-TileColor" content="{{ settings('color_1') }}">
    <meta name="theme-color" content="{{ settings('color_1') }}">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Saira:ital,wght@0,100..900;1,100..900&display=swap" rel="stylesheet">
    <link href="{{asset('crud/vendor/main/plugins.bundle.css')}}" rel="stylesheet" type="text/css"/>
    <link href="{{asset('crud/css/style.min.css')}}" rel="stylesheet" type="text/css"/>
    <link href="{{asset('crud/css/main/style.bundle.css')}}" rel="stylesheet" type="text/css"/>
    <link href="{{asset('crud/vendor/datatables/datatables.bundle.min.css')}}" rel="stylesheet" type="text/css"/>
    @yield('style')
    <style>
        :root
        {
            --primaryColor      : {{ settings('color_1') }};
            --primaryLightColor : color-mix(in srgb, var(--primaryColor) 2ƒ0%, white 100%);;
            --secondaryColor    : {{ settings('color_2') }};
        }
    </style>
</head>
<body id="kt_app_body" data-kt-app-header-fixed="true" data-kt-app-header-fixed-mobile="true" data-kt-app-sidebar-enabled="true" data-kt-app-sidebar-fixed="true" data-kt-app-sidebar-push-header="true" data-kt-app-sidebar-push-toolbar="true" data-kt-app-sidebar-push-footer="true" data-kt-app-aside-enabled="true" data-kt-app-aside-fixed="true" data-kt-app-aside-push-header="true" data-kt-app-aside-push-toolbar="true" data-kt-app-aside-push-footer="true" class="app-default">
<script>var defaultThemeMode = "light"; var themeMode; if ( document.documentElement ) { if ( document.documentElement.hasAttribute("data-bs-theme-mode")) { themeMode = document.documentElement.getAttribute("data-bs-theme-mode"); } else { if ( localStorage.getItem("data-bs-theme") !== null ) { themeMode = localStorage.getItem("data-bs-theme"); } else { themeMode = defaultThemeMode; } } if (themeMode === "system") { themeMode = window.matchMedia("(prefers-color-scheme: dark)").matches ? "dark" : "light"; } document.documentElement.setAttribute("data-bs-theme", themeMode); }</script>
<div class="d-flex flex-column flex-root app-root" id="kt_app_root">
    <div class="app-page flex-column flex-column-fluid" id="kt_app_page">
        <div id="kt_app_header" class="app-header d-flex d-lg-none border-bottom">
            <div class="app-container align-content-center container-fluid" id="kt_app_header_container">
                <div class="row">
                    <div class="col-4 align-content-center">
                        <button class="btn btn-icon btn-sm btn-active-color-primary ms-n2" id="kt_app_sidebar_mobile_toggle">
                            <i class="ki-outline ki-burger-menu-5"></i>
                        </button>
                    </div>
                    <div class="col-8 align-content-center">
                        <a href="{{route('dashboard')}}">
                            <img alt="Logo" src="{{ asset(settings('logo')) }}" class="h-40px theme-light-show" />
                        </a>
                    </div>
                </div>
            </div>
        </div>
        <div class="app-wrapper flex-column flex-row-fluid" id="kt_app_wrapper">

            <div id="kt_app_sidebar" class="app-sidebar" data-kt-drawer="true" data-kt-drawer-name="app-sidebar" data-kt-drawer-activate="{default: true, lg: false}" data-kt-drawer-overlay="true" data-kt-drawer-width="225px" data-kt-drawer-direction="start" data-kt-drawer-toggle="#kt_app_sidebar_mobile_toggle">
                <div class="app-sidebar-logo d-flex align-center justify-content-center px-9 pt-10 pb-5" id="kt_app_sidebar_logo">
                    <a href="{{route('dashboard')}}">
                        <img alt="Logo" src="{{ asset(settings('logo')) }}" class="h-50px theme-light-show" />
                    </a>
                </div>
                <div class="app-sidebar-menu flex-column-fluid px-7">
                    <!--begin::Menu wrapper-->
                    <div id="kt_app_sidebar_menu_wrapper" class="hover-scroll-y my-5" data-kt-scroll="true" data-kt-scroll-activate="true" data-kt-scroll-height="auto" data-kt-scroll-dependencies="#kt_app_sidebar_logo, #kt_app_sidebar_footer" data-kt-scroll-wrappers="#kt_app_sidebar_menu" data-kt-scroll-offset="5px">
                        <!--begin::Primary menu-->
                        <div class="menu menu-column menu-rounded menu-sub-indention fw-semibold" id="#kt_app_sidebar_menu" data-kt-menu="true" data-kt-menu-expand="false">

                            <div data-kt-menu-trigger="click" class="menu-item menu-accordion">
                                <a href="{{route('dashboard')}}" class="menu-link {{request()->routeIs('dashboard*') ? 'active' : null}}">
                                    <span class="menu-icon"><i class="ki-outline ki-category fs-2"></i></span>
                                    <span class="menu-title">Dashboard</span>
                                </a>
                            </div>

                            @if(count($menus) > 0)
                                @foreach($menus as $menu)
                                    @if(auth()->user()->hasPermission($menu->route) || $menu->dynamic_route == 0 || auth()->user()->roleGroup->id == 1)
                                        {!! menuGenerate($menu) !!}
                                    @endif
                                @endforeach
                            @endif

                            <div class="separator separator-gray-300 separator-dashed my-3"></div>

                            @foreach($mainMenus as $mainMenu)
                                @if(auth()->user()->hasPermission($mainMenu->route) || $mainMenu->dynamic_route == 0 || auth()->user()->roleGroup->id == 1)
                                    {!! menuGenerate($mainMenu) !!}
                                @endif
                            @endforeach
                        </div>
                    </div>
                    <!--end::Menu wrapper-->
                </div>
                <!--end::sidebar menu-->
                <!--begin::Footer-->
                <div class="app-sidebar-footer d-flex flex-stack px-10 py-10" id="kt_app_sidebar_footer">
                    <!--begin::User-->
                    <div class="me-2">
                        <!--begin::User info-->
                        <div class="d-flex align-items-center" data-kt-menu-trigger="{default: 'click', lg: 'hover'}" data-kt-menu-overflow="true" data-kt-menu-placement="top-start">
                            <div class="d-flex flex-center cursor-pointer symbol symbol-circle symbol-40px">
                                <img src="{{asset('crud/images/avatar.png')}}" alt="image" />
                            </div>
                            <!--begin::Name-->
                            <div class="d-flex flex-column align-items-start justify-content-center ms-3">
                                <span class="text-gray-500 fs-8 fw-semibold">Merhaba</span>
                                <a href="#" class="text-gray-800 fs-7 fw-bold text-hover-primary">{{\Illuminate\Support\Facades\Auth::user()->name}}</a>
                            </div>
                            <!--end::Name-->
                        </div>
                    </div>
                    <!--end::User-->
                    <a href="{{route('logout')}}" class="btn btn-icon btn-color-gray-500 btn-active-color-primary me-n7">
                        <i class="ki-outline ki-exit-right fs-2"></i>
                    </a>
                </div>
                <!--end::Footer-->
            </div>
            <!--end::Sidebar-->
            <div class="app-main flex-column flex-row-fluid" id="kt_app_main">
                <!--begin::Content wrapper-->
                <div class="d-flex flex-column flex-column-fluid">
                    <div class="d-flex flex-column flex-column-fluid">
                        <div id="kt_app_toolbar" class="app-toolbar  pt-10 mb-3 ">
                            <div id="kt_app_toolbar_container" class="app-container  container-fluid d-flex align-items-stretch ">
                                <div class="app-toolbar-wrapper d-flex flex-stack flex-wrap gap-4 w-100">
                                    <div class="page-title d-flex flex-column justify-content-center gap-1 me-3">
                                        <h1 class="page-heading d-flex flex-column justify-content-center text-gray-900 fw-bold fs-3 m-0">
                                            {{$activePage ?? null}}
                                        </h1>
                                        <ul class="breadcrumb breadcrumb-separatorless fw-semibold fs-7 my-0">
                                            <li class="breadcrumb-item text-muted">
                                                <a href="{{route('dashboard')}}" class="text-muted text-hover-primary">
                                                    Dashboard
                                                </a>
                                            </li>
                                            @if(isset($parentPage))
                                                <li class="breadcrumb-item"><span class="bullet bg-gray-500 w-5px h-2px"></span></li>
                                                <li class="breadcrumb-item text-muted">
                                                    <a href="{{$parentPageRoute}}" class="text-muted text-hover-primary"> {{$parentPage ?? null}} </a>
                                                </li>
                                            @endif
                                            @if(isset($activePage))
                                                <li class="breadcrumb-item"><span class="bullet bg-gray-500 w-5px h-2px"></span></li>
                                                <li class="breadcrumb-item text-muted"> {{$activePage ?? null}} </li>
                                            @endif
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div id="kt_app_content" class="app-content  flex-column-fluid ">
                            <div id="kt_app_content_container" class="app-container">
                                @yield('content')
                            </div>
                        </div>
                    </div>
                </div>
                <!--end::Content wrapper-->
                <!--begin::Footer-->
                <div id="kt_app_footer" class="app-footer">
                    <!--begin::Footer container-->
                    <div class="app-container container-fluid d-flex flex-column flex-md-row flex-center flex-md-stack py-3">
                        <!--begin::Copyright-->
                        <div class="text-gray-900 order-2 order-md-1">
                            <span class="text-muted fw-semibold me-1">2026&copy;</span>
                            <a href="https://www.zaurac.io" target="_blank" class="text-gray-800 text-hover-primary">zaurac.io</a>
                        </div>
                        <!--end::Copyright-->
                        <!--begin::Menu-->
                        <ul class="menu menu-gray-600 menu-hover-primary fw-semibold order-1">
                            <li class="menu-item">
                                <a href="https://www.zaurac.io/hakkimizda" target="_blank" class="menu-link px-2">Hakkımızda</a>
                            </li>
                            <li class="menu-item">
                                <a href="#" target="_blank" class="menu-link px-2">Destek</a>
                            </li>
                        </ul>
                        <!--end::Menu-->
                    </div>
                    <!--end::Footer container-->
                </div>
                <!--end::Footer-->
            </div>
            <!--end:::Main-->
        </div>
        <!--end::Wrapper-->
    </div>
    <!--end::Page-->
</div>


<div id="kt_scrolltop" class="scrolltop" data-kt-scrolltop="true">
    <i class="ki-outline ki-arrow-up"></i>
</div>
<input type="hidden" class="editor_upload_url" value="{{route('ckeditor.imageUpload')}}">
<script src="{{asset('crud/vendor/main/plugins.bundle.js')}}"></script>
<script src="{{asset('crud/js/main/scripts.bundle.js')}}"></script>
<script src="{{asset('crud/vendor/fslightbox/fslightbox.bundle.js')}}"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.29.2/moment.min.js"></script>
<script src="{{asset('crud/vendor/datatables/datatables.bundle.min.js')}}"></script>
<script src="{{asset('crud/vendor/tinymce/tinymce.min.js')}}"></script>
<script src="https://code.jquery.com/ui/1.13.2/jquery-ui.min.js"></script>
<script src="https://raw.githubusercontent.com/johnny/jquery-sortable/master/source/js/jquery-sortable-min.js"></script>
@yield('js')
<script src="{{asset('crud/js/script.min.js')}}"></script>
<script>
    $(function()
    {
        @if (trim($__env->yieldContent('datatables.columns')))
        const documentTitle = '@yield('datatables.files.title')';
        var datatables = $('#data-tables').DataTable({
            dom: 'Bflrtip',
            processing: true,
            serverSide: true,
            searchDelay: 500,
            scrollX: true,
            autoWidth: false,
            order: [],
            pageLength: 25,
            lengthMenu: [
                [10, 25, 50, 100, -1],
                [10, 25, 50, 100, "Hepsi"]
            ],
            columnDefs:
                [
                    {
                        targets: -1,
                        orderable: false,
                    },
                    { targets: "_all", width: "auto" }
                ],
            buttons: [
                {
                    extend: 'excelHtml5',
                    title: documentTitle,
                    exportOptions: {
                        columns: @yield('datatables.files.columns')
                    }
                },
                {
                    extend: 'pdfHtml5',
                    title: documentTitle,
                    exportOptions: {
                        columns: @yield('datatables.files.columns')
                    }
                }
            ],
            ajax : '@yield('datatables.ajax.url')',
            columns : @yield('datatables.columns'),
            language:{"url":"{{asset('crud/vendor/datatables/turkish.json')}}"},
            drawCallback : function( settings )
            {
                KTMenu.createInstances();
                $('.dt-buttons').addClass('d-none');
                $('.dt-buttons + div').addClass('d-none');
                $('.dataTables_filter').addClass('d-none');
                $('[name="data-tables_length"]').closest('div').addClass('d-inline-block');
                $('[name="data-tables_length"]').closest('label').addClass('dt-length');
            }
        });

        function dtFixHeaderWidths()
        {
            $('.dataTable thead tr th').each(function (i) {
                let bodyCell = $('.dataTable tbody tr:first td').eq(i);
                if (bodyCell.length) {
                    $(this).css('width', bodyCell.outerWidth() + 'px');
                }
            });
        }

        datatables.on('draw.dt', dtFixHeaderWidths);


        $(window).on('resize', function () {
            dtFixHeaderWidths();
        });

        let filterSearch = document.querySelector('[data-kt-data-table-filter="search"]');


        filterSearch.addEventListener('keyup', function (e)
        {
            datatables.search(e.target.value).draw();
        });

        const exportButtons = document.querySelectorAll('#exportButtons [data-table-export-button-name]');
        exportButtons.forEach(exportButton => {
            exportButton.addEventListener('click', e => {
                e.preventDefault();

                // Get clicked export value
                const exportValue = e.target.getAttribute('data-table-export-button-name');

                $('.dt-buttons .buttons-' + exportValue).trigger('click');
            });
        });
        @endif
    });
</script>
</body>
</html>