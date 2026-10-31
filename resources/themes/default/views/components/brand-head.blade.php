@props([
    'title'       => null,
    'description' => null,
])

@php
    $pageTitle   = $title ? $title . ' · ' . crudBrand('name') : crudBrand('name');
    $pageSummary = $description ?: crudBrand('description');
@endphp

<title>{{ $pageTitle }}</title>

<meta name="description" content="{{ $pageSummary }}">
<meta name="theme-color" content="{{ crudBrand('color.primary') }}">

<link rel="icon" href="{{ asset('favicon.ico') }}" sizes="any">
<link rel="icon" type="image/png" sizes="32x32" href="{{ asset(crudBrand('logo.favicon')) }}">
<link rel="icon" type="image/png" sizes="192x192" href="{{ asset(crudBrand('logo.icon')) }}">
<link rel="apple-touch-icon" sizes="180x180" href="{{ asset(crudBrand('logo.touch')) }}">

<meta property="og:type" content="website">
<meta property="og:site_name" content="{{ crudBrand('name') }}">
<meta property="og:title" content="{{ $pageTitle }}">
<meta property="og:description" content="{{ $pageSummary }}">
<meta property="og:url" content="{{ url()->current() }}">
<meta property="og:image" content="{{ asset(crudBrand('logo.share')) }}">
<meta property="og:image:width" content="1200">
<meta property="og:image:height" content="630">
<meta name="twitter:card" content="summary_large_image">
<meta name="twitter:title" content="{{ $pageTitle }}">
<meta name="twitter:description" content="{{ $pageSummary }}">
<meta name="twitter:image" content="{{ asset(crudBrand('logo.share')) }}">
