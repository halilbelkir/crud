@php
    $variables = [
        '--brand-primary'  => crudBrand('color.primary'),
        '--brand-accent'   => crudBrand('color.accent'),
        '--brand-ink'      => crudBrand('color.ink'),
        '--text-muted'     => crudBrand('color.muted'),
        '--line-1'         => crudBrand('color.line'),
        '--surface-subtle' => crudBrand('color.subtle'),
        '--surface-app'    => crudBrand('color.app'),

        '--brand-font-heading' => crudBrand('font.heading'),
        '--brand-font-body'    => crudBrand('font.body'),
        '--brand-font-label'   => crudBrand('font.label'),
    ];
@endphp

@if (crudBrand('font.url'))
    <link rel="stylesheet" href="{{ crudBrand('font.url') }}">
@endif

@if (crudBrand('font.custom'))
    <link rel="stylesheet" href="{{ crudBrand('font.custom') }}">
@endif

<style>
    :root {
        @foreach (array_filter($variables) as $name => $value){{ $name }}: {!! $value !!};
        @endforeach
    }
</style>
