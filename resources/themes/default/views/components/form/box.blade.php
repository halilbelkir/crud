@props([
    'size'     => 'md',
    'error'    => null,
    'valid'    => false,
    'disabled' => false,
    'multi'    => false,
])

@php
    $sizes = [
        'md'   => 'h-field rounded-[26px] px-5',
        'dense' => 'h-[46px] rounded-[23px] px-4 text-sm lg:h-field-dense lg:rounded-[22px] lg:px-[17px]',
        'sm'   => 'h-field-sm rounded-[20px] px-4 text-sm',
        'auth' => 'h-field-auth rounded-[30px] px-5',
        'area' => 'rounded-[20px] px-5 py-4',
    ];

    $state = match (true)
    {
        (bool) $disabled => 'bg-line-soft border-transparent text-muted cursor-not-allowed',
        (bool) $error    => 'bg-tint-danger border-danger',
        (bool) $valid    => 'bg-card border-success',
        default          => 'bg-subtle border-line-2 hover:border-line-3 focus-within:bg-card focus-within:border-primary focus-within:ring-1 focus-within:ring-primary focus-within:ring-inset',
    };
@endphp

<div data-box {{ $attributes->class([
    'flex gap-3 border transition-colors',
    $multi ? 'items-start' : 'items-center',
    $sizes[$size] ?? $sizes['md'],
    $state,
]) }}>
    {{ $slot }}
</div>
