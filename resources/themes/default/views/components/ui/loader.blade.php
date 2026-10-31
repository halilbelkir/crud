@props([
    'variant' => 'default',
    'size'    => null,
])

@php
    $sizes = [
        'default'     => 48,
        'standart'    => 40,
        'small'       => 24,
        'tiny'        => 16,
        'button'      => 20,
        'light'       => 48,
        'light_small' => 24,
    ];

    $pixels = $size ?? ($sizes[$variant] ?? 48);
    $custom = crudBrand('loader.' . $variant, crudBrand('loader.default'));
    $clip   = \Illuminate\Support\Str::random(8);
    $top    = 'M295.7,79.49v216.21L0,0h216.21c43.9,0,79.49,35.59,79.49,79.49Z';
    $bottom = 'M166.15,382.36h216.21L86.65,86.65v216.21c0,43.9,35.59,79.49,79.49,79.49Z';
@endphp

@if ($custom)
    <img
        x-data
        :src="window.loaderSource({{ \Illuminate\Support\Js::from($custom) }})"
        data-loader-image="{{ $custom }}"
        alt=""
        {{ $attributes->class('shrink-0') }}
        style="width: {{ $pixels }}px; height: {{ $pixels }}px"
    >
@else
    <svg
        viewBox="0 0 382.36 382.36"
        width="{{ $pixels }}"
        height="{{ $pixels }}"
        role="img"
        aria-label="Yükleniyor"
        data-loader-mark
        {{ $attributes->class('zd shrink-0') }}
    >
        <defs>
            <clipPath id="zd-top-{{ $clip }}">
                <path d="{{ $top }}"></path>
            </clipPath>

            <clipPath id="zd-bottom-{{ $clip }}">
                <path d="{{ $bottom }}"></path>
            </clipPath>
        </defs>

        <path class="zl-slot" d="{{ $top }}"></path>
        <path class="zl-slot" d="{{ $bottom }}"></path>
        <path class="zd-line zd-top" d="{{ $top }}" pathLength="100" clip-path="url(#zd-top-{{ $clip }})"></path>
        <path class="zd-line zd-bottom" d="{{ $bottom }}" pathLength="100" clip-path="url(#zd-bottom-{{ $clip }})"></path>
    </svg>
@endif
