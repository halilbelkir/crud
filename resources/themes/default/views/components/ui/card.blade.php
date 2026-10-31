@props([
    'title'    => null,
    'subtitle' => null,
    'flush'    => false,
    'fill'     => false,
])

<div {{ $attributes->class([
    'bg-card border border-line rounded-card',
    'overflow-hidden' => $flush,
    'flex flex-col'   => $fill,
]) }}>
    @if ($title || $subtitle || isset($actions))
        <div @class([
            'flex flex-wrap items-center gap-x-4 gap-y-2',
            'px-[18px] py-3.5 border-b border-line-softer' => $flush,
            'px-5 pt-5' => ! $flush,
        ])>
            <div class="min-w-0">
                @if ($title)
                    <div class="font-label text-sm font-semibold truncate">{{ $title }}</div>
                @endif

                @if ($subtitle)
                    <div class="text-xs text-muted mt-0.5 truncate">{{ $subtitle }}</div>
                @endif
            </div>

            @isset($actions)
                <div class="ml-auto flex items-center gap-2.5 min-w-0 max-w-full">{{ $actions }}</div>
            @endisset
        </div>
    @endif

    <div @class([
        'px-5 py-5' => ! $flush,
        'grow flex flex-col justify-center' => $fill,
    ])>
        {{ $slot }}
    </div>
</div>
