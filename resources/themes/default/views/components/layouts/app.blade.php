@props([
    'title'      => null,
    'breadcrumb' => [],
])

<!DOCTYPE html>
<html lang="tr" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="robots" content="noindex, nofollow">

    <x-crudPackage::brand-head :title="$title" />

    @if (crudBrand('loader.default'))
        <link rel="preload" as="image" href="{{ asset(crudBrand('loader.default')) }}">
    @endif

    <link rel="stylesheet" href="{{ \crudPackage\Library\Theme\ThemeManager::asset('app.css') }}">
    <script type="module" src="{{ \crudPackage\Library\Theme\ThemeManager::asset('app.js') }}"></script>

    <x-crudPackage::brand-theme />

    <script>
        if (localStorage.getItem('ui.sidebar') === 'collapsed') { document.documentElement.classList.add('menu-collapsed'); }

        (() => {
            const stored = JSON.parse(localStorage.getItem('ui.menu.closed') || 'null');
            const closed = Array.isArray(stored) ? stored : @js(\crudPackage\Support\Menu::collapsedByDefault());

            if (! closed.length) { return; }

            const rules = closed
                .map(group => 'html:not(.menu-collapsed) [data-menu-group="' + String(group).replace(/["\\]/g, '\\$&') + '"]:not([data-menu-active])')
                .join(',');

            document.head.insertAdjacentHTML('beforeend', '<style id="menu-preset">' + rules + '{display:none}</style>');
        })();
    </script>
</head>
<body class="h-full overflow-hidden bg-app text-ink font-body antialiased">

    <template data-loader-template>
        <x-crudPackage::ui.loader variant="button" />
    </template>

    <div class="flex h-dvh overflow-hidden">
        <x-crudPackage::panel.sidebar />

        <div class="flex flex-1 min-w-0 flex-col h-full">
            <x-crudPackage::panel.mobile-header />

            <x-crudPackage::panel.topbar :breadcrumb="$breadcrumb">
                {{ $actions ?? '' }}
            </x-crudPackage::panel.topbar>

            <main data-content class="relative flex-1 min-h-0 overflow-y-auto px-4 pt-3.5 pb-0 lg:px-3 lg:pt-4 lg:pb-3">
                {{ $slot }}
            </main>

            <x-crudPackage::panel.mobile-nav />
        </div>
    </div>

    <x-crudPackage::ui.form-drawer />

    <x-crudPackage::ui.media-preview />

    <x-crudPackage::ui.dialog />

    <x-crudPackage::ui.toast />

</body>
</html>
