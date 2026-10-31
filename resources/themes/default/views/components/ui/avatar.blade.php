@props([
    'name'   => '',
    'image'  => null,
    'size'   => 34,
    'tone'    => 'accent',
    'dashed'  => false,
    'contain' => false,
    'lead'    => false,
    'chip'    => false,
])

@php
    $tones = [
        'accent'  => 'bg-tint-accent-2 text-on-tint-accent',
        'primary' => 'bg-primary text-white',
        'dark'    => 'bg-ink-2 text-white',
    ];

    $words = \Illuminate\Support\Str::of($name)->explode(' ')->filter()->values();

    $picked = $words->count() > 1
        ? [$words->first(), $words->last()]
        : $words->all();

    $initials = $lead
        ? \Illuminate\Support\Str::upper(\Illuminate\Support\Str::substr(trim($name), 0, 2))
        : collect($picked)
            ->map(fn ($word) => \Illuminate\Support\Str::upper(\Illuminate\Support\Str::substr($word, 0, 1)))
            ->implode('');
@endphp

@if ($image && $chip)
    <span
        {{ $attributes->class(['rounded-full shrink-0 flex items-center justify-center', $tones[$tone] ?? $tones['accent']]) }}
        style="width: {{ $size }}px; height: {{ $size }}px; flex: 0 0 {{ $size }}px"
    >
        <img src="{{ $image }}" alt="{{ $name }}" class="object-contain" style="width: {{ round($size * 0.62) }}px; height: {{ round($size * 0.62) }}px">
    </span>
@elseif ($image)
    <img
        src="{{ $image }}"
        alt="{{ $name }}"
        {{ $attributes->class([
            'shrink-0',
            'object-contain' => $contain,
            'object-cover rounded-full' => ! $contain,
        ]) }}
        style="width: {{ $size }}px; height: {{ $size }}px; flex: 0 0 {{ $size }}px"
    >
@elseif (! is_numeric(trim((string) $name)))
    <span
        {{ $attributes->class([
            'rounded-full shrink-0 flex items-center justify-center font-label font-semibold',
            $tones[$tone] ?? $tones['accent'] => ! $dashed,
            'border-2 border-dashed border-disabled text-faint' => $dashed,
        ]) }}
        style="width: {{ $size }}px; height: {{ $size }}px; flex: 0 0 {{ $size }}px; font-size: {{ round($size * 0.38) }}px"
    >
        {{ $initials }}
    </span>
@endif
