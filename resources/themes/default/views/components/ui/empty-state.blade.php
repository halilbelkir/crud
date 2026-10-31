@props([
    'icon'    => 'search',
    'title'   => 'Sonuç bulunamadı',
    'message' => null,
])

<div {{ $attributes->class('flex flex-col items-center justify-center text-center gap-3 py-10 px-5') }}>
    <span class="w-[74px] h-[74px] rounded-full bg-app flex items-center justify-center text-disabled">
        <x-crudPackage::ui.icon :name="$icon" :size="32" :stroke="1.6" />
    </span>

    <div class="font-heading text-[22px]">{{ $title }}</div>

    @if ($message)
        <div class="text-sm text-muted max-w-[360px] leading-relaxed">{{ $message }}</div>
    @endif

    @isset($action)
        <div class="mt-1">{{ $action }}</div>
    @endisset
</div>
