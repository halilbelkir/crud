<!doctype html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>{{ settings('title') }}</title>
    <meta name="description" content="{{ settings('subtitle') }}">
    <link rel="shortcut icon" href="{{ asset(settings('icon')) }}" type="image/png">
    <meta name="msapplication-TileColor" content="{{ settings('color_1') }}">
    <meta name="theme-color" content="{{ settings('color_1') }}">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Saira:ital,wght@0,100..900;1,100..900&display=swap" rel="stylesheet">
    <link href="{{asset('crud/vendor/guest/plugins.bundle.min.css')}}" rel="stylesheet" type="text/css"/>
    <link href="{{asset('crud/css/guest/style.bundle.css')}}" rel="stylesheet" type="text/css"/>
    <link href="{{asset('crud/css/guest/style.min.css')}}" rel="stylesheet" type="text/css"/>
    <style>
        :root
        {
            --primaryColor   : {{ settings('color_1') }};
            --secondaryColor : {{ settings('color_2') }};
        }
    </style>
</head>
<body id="kt_body"  class="app-blank bgi-size-cover bgi-no-repeat bgi-position-top">
<script>
    var defaultThemeMode = "light";
    var themeMode;

    if ( document.documentElement ) {
        if ( document.documentElement.hasAttribute("data-bs-theme-mode")) {
            themeMode = document.documentElement.getAttribute("data-bs-theme-mode");
        } else {
            if ( localStorage.getItem("data-bs-theme") !== null ) {
                themeMode = localStorage.getItem("data-bs-theme");
            } else {
                themeMode = defaultThemeMode;
            }
        }

        if (themeMode === "system") {
            themeMode = window.matchMedia("(prefers-color-scheme: dark)").matches ? "dark" : "light";
        }

        document.documentElement.setAttribute("data-bs-theme", themeMode);
    }
</script>
<div class="d-flex flex-column flex-root" id="kt_app_root">
    <style>
        body {
            background-image: url('{{ asset(settings('bg_image')) }}');
        }

        [data-bs-theme="dark"] body {
            background-image: url('{{ asset(settings('bg_image')) }}');
        }
    </style>

    <div class="container-fluid">
        <div class="row">
            <div class=" col-xl-8 col-md-6 guestHead">
                <div class="guestTitle">
                    <h1 class="text-white fs-2qx fw-bold text-start">
                        {{ settings('title') }}
                    </h1>
                    <h3 class="text-white fw-bold text-start">
                        {{ settings('subtitle') }}
                    </h3>
                </div>
            </div>
            <div class="col-xl-3 col-md-5">
                <div class="guestSidebar">
                    <div class="bg-body rounded-4 p-10">
                        @yield('content')
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
</body>

<script src="{{asset('crud/vendor/guest/plugins.bundle.js')}}"></script>
<script src="{{asset('crud/js/guest/scripts.bundle.min.js')}}"></script>
<script src="{{asset('crud/js/script.min.js')}}"></script>
</html>