@props([
    'title',
    'meta'       => [],
    'image'      => null,
    'status'     => null,
    'statusTone' => 'success',
    'noAvatar'   => false,
    'contain'    => false,
    'verified'   => false,
])

@php
    $statusClass = 'h-[26px] rounded-[13px] px-3 text-[12.5px] ' . ($statusTone === 'success' ? 'bg-tint-success' : 'bg-neutral');
@endphp

<div class="flex flex-col gap-2.5 lg:block">

    <div class="bg-card border border-line rounded-card p-4 lg:px-[22px] lg:py-[18px] flex items-center gap-3.5 lg:gap-4">
        @unless ($noAvatar)
            <span class="lg:hidden">
                <x-crudPackage::ui.avatar :name="$title" :image="$image" :size="48" :tone="$contain ? 'accent' : 'primary'" :contain="$contain" />
            </span>

            <span class="hidden lg:block">
                <x-crudPackage::ui.avatar :name="$title" :image="$image" :size="72" tone="accent" :contain="$contain" />
            </span>
        @endunless

        <div class="min-w-0">
            <div class="flex items-center gap-2.5 lg:gap-[11px]">
                <div class="font-heading text-[19px] lg:text-[28px] leading-[1.15] truncate">{{ $title }}</div>

                @if ($verified)
                    <span class="hidden lg:flex w-[22px] h-[22px] shrink-0 rounded-full bg-success text-white items-center justify-center">
                        <x-crudPackage::ui.icon name="check" :size="13" :stroke="2.6" />
                    </span>
                @endif

                @isset($badge)
                    <span class="hidden lg:inline-flex shrink-0">{{ $badge }}</span>
                @endisset

                @if ($status)
                    <span class="hidden lg:inline-flex shrink-0">
                        <x-crudPackage::ui.status :tone="$statusTone" class="{{ $statusClass }}">{{ $status }}</x-crudPackage::ui.status>
                    </span>
                @endif
            </div>

            @if ($meta)
                <div class="text-[12.5px] lg:text-[13.5px] text-muted mt-0.5 lg:mt-1 truncate">{{ implode(' · ', array_filter($meta)) }}</div>
            @endif
        </div>

        @if ($status)
            <span class="lg:hidden ml-auto shrink-0">
                <x-crudPackage::ui.status :tone="$statusTone" class="{{ $statusClass }}">{{ $status }}</x-crudPackage::ui.status>
            </span>
        @endif

        @isset($actions)
            <div class="hidden lg:flex ml-auto items-center gap-2.5 shrink-0">{{ $actions }}</div>
        @endisset
    </div>

    @isset($actions)
        <div class="lg:hidden flex flex-wrap gap-2">{{ $actions }}</div>
    @endisset

</div>
