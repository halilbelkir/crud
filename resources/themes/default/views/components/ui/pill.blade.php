@props([
    'tone'   => 'neutral',
    'icon'   => null,
    'href'   => null,
    'active' => false,
])

@php
    $tones = [
        'primary' => 'bg-tint-primary text-primary',
        'neutral' => 'bg-neutral text-body',
        'outline' => 'border border-line-2 text-ink',
    ];

    $tone = $active ? 'primary' : $tone;

    $tag = $href ? 'a' : 'span';
@endphp

<{{ $tag }}
    @if ($href) href="{{ $href }}" @endif
    {{ $attributes->class([
        'inline-flex items-center gap-2 h-[34px] rounded-[17px] px-[15px] font-label text-[11px] font-semibold tracking-[0.08em] whitespace-nowrap',
        $tones[$tone] ?? $tones['neutral'],
    ]) }}
>
    @if ($icon)
        <x-crudPackage::ui.icon :name="$icon" :size="14" class="shrink-0" />
    @endif

    {{ $slot }}
</{{ $tag }}>
