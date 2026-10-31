@props([
    'variant' => 'primary',
    'size'    => 'md',
    'href'    => null,
    'icon'    => null,
    'trailing' => null,
])

@php
    $variants = [
        'primary' => 'bg-primary text-white hover:opacity-90',
        'accent'  => 'bg-accent text-white hover:opacity-90',
        'dark'    => 'bg-ink text-white hover:opacity-90',
        'outline' => 'border border-line-2 text-body hover:border-line-3 hover:text-ink',
        'outline-danger' => 'border border-tint-danger-strong text-danger hover:bg-tint-danger',
        'outline-light'  => 'border border-white/60 text-white hover:border-white hover:bg-white/10',
        'ghost'   => 'text-body hover:bg-app',
        'danger'  => 'bg-danger text-white hover:opacity-90',
    ];

    $sizes = [
        'lg' => 'h-[60px] rounded-[30px] px-8 text-sm',
        'md' => 'h-btn rounded-3xl px-[26px] text-xs',
        'sm' => 'h-ctl rounded-[20px] px-[18px] text-xs',
        'xs' => 'h-[38px] rounded-[19px] px-[15px] text-[11px] tracking-[0.08em]',
        'field' => 'h-field rounded-[26px] px-[26px] text-xs',
    ];

    $classes = implode(' ', [
        'inline-flex items-center justify-center gap-2.5 font-label font-semibold tracking-[0.1em] uppercase whitespace-nowrap transition-opacity',
        'disabled:bg-line-soft disabled:text-disabled disabled:border-transparent disabled:opacity-100 disabled:cursor-not-allowed',
        $variants[$variant] ?? $variants['primary'],
        $sizes[$size] ?? $sizes['md'],
    ]);

    $tag = $href ? 'a' : 'button';
@endphp

<{{ $tag }}
    @if ($href) href="{{ $href }}" @else type="{{ $attributes->get('type', 'button') }}" @endif
    {{ $attributes->class($classes)->except('type') }}
>
    @if ($icon)
        <x-crudPackage::ui.icon :name="$icon" :size="17" class="shrink-0" />
    @endif

    @if (trim($slot) !== '')
        <span>{{ $slot }}</span>
    @endif

    @if ($trailing === 'plus-plain')
        <span class="text-[15px] tracking-normal">+</span>
    @elseif ($trailing)
        <span class="w-5 h-5 rounded-full bg-white/20 flex items-center justify-center text-[13px] tracking-normal">
            {{ $trailing === 'arrow' ? '→' : '+' }}
        </span>
    @endif
</{{ $tag }}>
