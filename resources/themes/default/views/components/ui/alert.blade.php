@props([
    'tone'  => 'neutral',
    'title' => null,
    'icon'  => null,
])

@php
    $tones = [
        'success' => ['box' => 'bg-tint-success text-on-tint-success', 'icon' => 'check-circle'],
        'primary' => ['box' => 'bg-tint-primary text-primary',         'icon' => 'alert'],
        'danger'  => ['box' => 'bg-tint-danger text-danger',           'icon' => 'alert'],
        'accent'  => ['box' => 'bg-tint-accent text-on-tint-accent',   'icon' => 'alert'],
        'warning' => ['box' => 'bg-tint-warning border border-line-warning text-on-tint-warning', 'icon' => 'alert'],
        'neutral' => ['box' => 'bg-app text-body',                     'icon' => 'alert'],
        'outline' => ['box' => 'border border-line-soft text-body !rounded-[14px] !px-[14px] !py-[11px] text-[13px]', 'icon' => 'lock'],
    ];

    $current = $tones[$tone] ?? $tones['neutral'];
@endphp

<div {{ $attributes->class(['flex items-start gap-3 rounded-alert px-5 py-4', $current['box']]) }}>
    <x-crudPackage::ui.icon :name="$icon ?? $current['icon']" :size="19" class="shrink-0 mt-0.5" />

    <div class="min-w-0 flex-1">
        @if ($title)
            <div class="font-label text-sm font-semibold">{{ $title }}</div>
        @endif

        <div class="text-sm leading-relaxed {{ $title ? 'mt-1' : '' }}">{{ $slot }}</div>
    </div>
</div>
