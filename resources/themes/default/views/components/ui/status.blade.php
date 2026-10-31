@props([
    'tone' => 'success',
    'dot'  => null,
])

@php
    $tones = [
        'success' => ['text' => 'text-on-tint-success', 'dot' => 'bg-on-tint-success'],
        'muted'   => ['text' => 'text-muted',           'dot' => 'bg-disabled'],
        'primary' => ['text' => 'text-primary',         'dot' => 'bg-primary'],
        'accent'  => ['text' => 'text-accent',          'dot' => 'bg-accent'],
        'danger'  => ['text' => 'text-danger',          'dot' => 'bg-danger'],
    ];

    $current = $tones[$tone] ?? $tones['success'];
@endphp

<span {{ $attributes->class(['inline-flex items-center gap-[7px] text-[13px] whitespace-nowrap', $current['text']]) }}>
    <span class="w-2 h-2 rounded-full shrink-0 {{ $current['dot'] }}" @if ($dot) style="background: {{ $dot }}" @endif></span>
    {{ $slot }}
</span>
