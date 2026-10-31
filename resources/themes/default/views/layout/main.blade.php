@php
    $breadcrumb = array_values(array_filter(
    [
        isset($parentPage) ? ['label' => $parentPage, 'url' => $parentPageRoute ?? null] : null,
        ['label' => $activePage ?? ''],
    ]));
@endphp

<x-crudPackage::layouts.app :title="$activePage ?? null" :breadcrumb="$breadcrumb">
    @yield('style')
    @yield('content')
    @yield('js')
</x-crudPackage::layouts.app>
