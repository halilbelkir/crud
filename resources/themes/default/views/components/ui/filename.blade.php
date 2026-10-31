@props([
    'path',
])

@php
    $basename  = basename($path);
    $extension = pathinfo($basename, PATHINFO_EXTENSION);
    $stem      = filled($extension) ? mb_substr($basename, 0, -mb_strlen($extension) - 1) : $basename;
@endphp

<span {{ $attributes->class(['flex min-w-0']) }} title="{{ $basename }}">
    <span class="truncate">{{ $stem }}</span>

    @if (filled($extension))
        <span class="shrink-0">.{{ $extension }}</span>
    @endif
</span>
