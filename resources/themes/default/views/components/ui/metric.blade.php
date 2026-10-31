@props([
    'label',
    'value'   => 0,
    'percent' => 0,
    'tone'    => 'primary',
    'filled'  => false,
    'note'    => null,
])

@php
    $radius        = 21.5;
    $circumference = 2 * M_PI * $radius;
    $progress      = $circumference * min(100, max(0, (int) $percent)) / 100;

    $strokes = [
        'primary' => 'var(--color-primary)',
        'accent'  => 'var(--color-accent)',
        'ink'     => 'var(--color-ink)',
        'success' => 'var(--color-success)',
    ];
@endphp

<div {{ $attributes->class([
    'flex items-center gap-3 p-3.5 lg:p-4 lg:px-[18px]',
    'bg-primary text-white rounded-[20px]' => $filled,
    'bg-card border border-line rounded-card' => ! $filled,
]) }}>
    <svg viewBox="0 0 52 52" class="w-12 h-12 lg:w-[52px] lg:h-[52px] shrink-0 block">
        <circle cx="26" cy="26" r="{{ $radius }}" fill="none" stroke="{{ $filled ? 'rgba(255,255,255,0.25)' : 'var(--color-line-soft)' }}" stroke-width="8" />
        <circle
            cx="26"
            cy="26"
            r="{{ $radius }}"
            fill="none"
            stroke="{{ $filled ? '#fff' : ($strokes[$tone] ?? $strokes['primary']) }}"
            stroke-width="8"
            stroke-linecap="round"
            stroke-dasharray="{{ number_format($progress, 1, '.', '') }} {{ number_format($circumference, 1, '.', '') }}"
            transform="rotate(-90 26 26)"
        />
    </svg>

    <div class="min-w-0">
        <div class="font-heading text-[22px] lg:text-[26px] leading-none">{{ $value }}</div>
        <div class="text-xs mt-0.5 {{ $filled ? 'text-white/80' : 'text-muted' }}">{{ $label }}</div>
    </div>

    @if ($note)
        <span class="ml-auto font-label text-xs font-semibold {{ $filled ? 'text-white' : 'text-primary' }}">{{ $note }}</span>
    @endif
</div>
