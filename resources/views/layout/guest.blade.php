<!doctype html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Zaurac Teknoloji</title>
    <link rel="apple-touch-icon" sizes="57x57" href="{{asset('crud/images/fav/apple-icon-57x57.png')}}">
    <link rel="apple-touch-icon" sizes="60x60" href="{{asset('crud/images/fav/apple-icon-60x60.png')}}">
    <link rel="apple-touch-icon" sizes="72x72" href="{{asset('crud/images/fav/apple-icon-72x72.png')}}">
    <link rel="apple-touch-icon" sizes="76x76" href="{{asset('crud/images/fav/apple-icon-76x76.png')}}">
    <link rel="apple-touch-icon" sizes="114x114" href="{{asset('crud/images/fav/apple-icon-114x114.png')}}">
    <link rel="apple-touch-icon" sizes="120x120" href="{{asset('crud/images/fav/apple-icon-120x120.png')}}">
    <link rel="apple-touch-icon" sizes="144x144" href="{{asset('crud/images/fav/apple-icon-144x144.png')}}">
    <link rel="apple-touch-icon" sizes="152x152" href="{{asset('crud/images/fav/apple-icon-152x152.png')}}">
    <link rel="apple-touch-icon" sizes="180x180" href="{{asset('crud/images/fav/apple-icon-180x180.png')}}">
    <link rel="icon" type="image/png" sizes="192x192"  href="{{asset('crud/images/fav/android-icon-192x192.png')}}">
    <link rel="icon" type="image/png" sizes="32x32" href="{{asset('crud/images/fav/favicon-32x32.png')}}">
    <link rel="icon" type="image/png" sizes="96x96" href="{{asset('crud/images/fav/favicon-96x96.png')}}">
    <link rel="icon" type="image/png" sizes="16x16" href="{{asset('crud/images/fav/favicon-16x16.png')}}">
    <link rel="manifest" href="{{asset('crud/images/fav/manifest.json')}}">
    <meta name="msapplication-TileColor" content="#c21b17">
    <meta name="msapplication-TileImage" content="{{asset('crud/images/fav/ms-icon-144x144.png')}}">
    <meta name="theme-color" content="#c21b17">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Saira:ital,wght@0,100..900;1,100..900&display=swap" rel="stylesheet">
    <link href="{{asset('crud/vendor/guest/plugins.bundle.min.css')}}" rel="stylesheet" type="text/css"/>
    <link href="{{asset('crud/css/guest/style.bundle.css')}}" rel="stylesheet" type="text/css"/>
    <link href="{{asset('crud/css/guest/style.min.css')}}" rel="stylesheet" type="text/css"/>
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
            background-image: url('{{asset('crud/images/guest.jpg')}}');
        }

        [data-bs-theme="dark"] body {
            background-image: url('{{asset('crud/images/guest.jpg')}}');
        }
    </style>

    <div class="container-fluid">
        <div class="row">
            <div class=" col-xl-8 col-md-6 guestHead">
                <div class="guestTitle">
                    <h1 class="text-white fs-2qx fw-bold text-start">
                        Zaurac Teknoloji
                    </h1>
                    <h3 class="text-white fw-bold text-start">
                        Yönetim Paneline Hoş Geldiniz
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