@props([
    'breadcrumb' => [],
])

@php
    use crudPackage\Support\Menu;

    $quick = config('crudPanel.quick_action');
@endphp

<header class="hidden lg:flex h-topbar shrink-0 mx-3 mt-3 bg-card border border-line rounded-card items-center gap-[18px] px-7">
    @if ($breadcrumb)
        <div class="text-sm text-muted flex items-center gap-2 min-w-0">
            @foreach ($breadcrumb as $crumb)
                @if (! $loop->first)
                    <span class="text-disabled">›</span>
                @endif

                @if (! $loop->last && ($crumb['url'] ?? null))
                    <a href="{{ $crumb['url'] }}" class="hover:text-ink truncate">{{ $crumb['label'] }}</a>
                @else
                    <span @class(['text-ink truncate' => $loop->last, 'truncate' => ! $loop->last])>{{ $crumb['label'] }}</span>
                @endif
            @endforeach
        </div>
    @endif

    <div class="ml-auto flex items-center gap-[18px]">
        @if ($quick && Menu::allowed($quick))
            <x-crudPackage::ui.button size="xs" trailing="plus" :href="route($quick['route'])">
                {{ $quick['label'] }}
            </x-crudPackage::ui.button>
        @endif

        {{ $slot }}
    </div>
</header>
