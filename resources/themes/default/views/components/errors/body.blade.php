@props([
    'code'    => 500,
    'icon'    => 'alert',
    'title'   => null,
    'message' => null,
    'href'    => '/',
    'label'   => 'Panele dön',
    'stacked' => false,
])

@php
    $wrapper = $stacked
        ? 'flex flex-col-reverse items-start gap-9'
        : 'grow flex flex-col-reverse lg:flex-row items-center justify-center gap-10 lg:gap-20 px-8 py-14 lg:px-20 lg:py-16';

    $ring = $stacked
        ? 'w-[200px] h-[200px]'
        : 'w-[220px] h-[220px] lg:w-[320px] lg:h-[320px]';

    $inner = $stacked
        ? 'inset-[28px]'
        : 'inset-[30px] lg:inset-[44px]';

    $number = $stacked
        ? 'text-[64px]'
        : 'text-[72px] lg:text-[104px]';

    $badge = $stacked
        ? 'w-[46px] h-[46px] right-1 bottom-2'
        : 'w-[52px] h-[52px] lg:w-[62px] lg:h-[62px] right-1 bottom-3 lg:right-2 lg:bottom-4';
@endphp

<div class="relative {{ $wrapper }}">
    <div class="w-full max-w-[460px] shrink-0">
        <div class="w-14 h-[5px] bg-accent"></div>

        <h1 class="font-heading text-[30px] lg:text-[46px] leading-[1.1] lg:leading-[1.06] mt-6">{{ $title }}</h1>

        <p class="text-[15px] lg:text-[17px] leading-[1.5] lg:leading-[1.55] text-body mt-1.5 lg:mt-2.5">{{ $message }}</p>

        <div class="mt-6 lg:mt-8">
            <x-crudPackage::ui.button :href="$href" size="lg">{{ $label }}</x-crudPackage::ui.button>
        </div>
    </div>

    <div class="relative shrink-0 flex items-center justify-center {{ $ring }}">
        <span class="absolute inset-0 rounded-full border-[3px] border-tint-accent-strong"></span>
        <span class="absolute {{ $inner }} rounded-full border-[3px] border-tint-accent"></span>

        <span class="font-heading {{ $number }} leading-none text-primary">{{ $code }}</span>

        <span class="absolute {{ $badge }} rounded-full bg-tint-accent text-on-tint-accent flex items-center justify-center">
            <x-crudPackage::ui.icon :name="$icon" :size="24" :stroke="1.6" />
        </span>
    </div>
</div>
