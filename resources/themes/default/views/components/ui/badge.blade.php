@props([
    'tone' => 'accent',
    'size' => 'md',
])

@php
    $tones = [
        'accent'  => 'bg-tint-accent-2 text-on-tint-accent',
        'gold'    => 'bg-tint-accent-strong text-on-tint-accent',
        'primary' => 'bg-tint-primary text-primary',
        'success' => 'bg-tint-success text-on-tint-success',
        'neutral' => 'bg-neutral text-body',
        'warning' => 'bg-tint-warning text-on-tint-warning',
        'danger'  => 'bg-tint-danger text-on-tint-danger',
    ];

    $sizes = [
        'md' => 'h-[26px] rounded-[13px] px-3 text-[11px]',
        'sm' => 'h-[22px] rounded-[11px] px-[10px] text-[9.5px] tracking-[0.08em]',
        'xs' => 'h-5 rounded-[10px] px-[7px] text-[9px]',
    ];
@endphp

<span {{ $attributes->class([
    'inline-flex items-center font-label font-semibold whitespace-nowrap',
    $sizes[$size] ?? $sizes['md'],
    $tones[$tone] ?? $tones['accent'],
]) }}>
    {{ $slot }}
</span>
