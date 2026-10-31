@props([
    'label',
    'muted'   => false,
    'stacked' => false,
])

@if ($stacked)
    <div {{ $attributes->class('min-w-0') }}>
        <div class="label-caps mb-2.5" style="letter-spacing: 0.1em">{{ $label }}</div>

        <div @class(['text-base font-semibold flex items-center gap-2.5 min-w-0', 'font-normal text-muted' => $muted])>
            {{ $slot }}
        </div>
    </div>
@else
    <div class="grid sm:grid-cols-[200px_1fr] items-center gap-x-3 min-h-14 py-2.5 sm:py-0 border-b border-line-softer last:border-b-0">
        <span class="text-[13.5px] text-muted min-w-0">{{ $label }}</span>

        <span @class(['text-[15px] flex items-center gap-2.5 min-w-0 sm:py-3', 'text-muted' => $muted])>
            {{ $slot }}
        </span>
    </div>
@endif
