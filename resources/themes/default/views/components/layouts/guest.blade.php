@props([
    'title'    => null,
    'eyebrow'  => null,
    'heading'  => null,
    'subtitle' => null,
    'image'    => null,
    'tagline'  => null,
    'back'     => null,
])

<!DOCTYPE html>
<html lang="tr" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="robots" content="noindex, nofollow">

    <x-crudPackage::brand-head :title="$title" />

    <link rel="stylesheet" href="{{ \crudPackage\Library\Theme\ThemeManager::asset('app.css') }}">
    <script type="module" src="{{ \crudPackage\Library\Theme\ThemeManager::asset('app.js') }}"></script>

    <x-crudPackage::brand-theme />
</head>
<body class="h-full bg-card text-ink font-body antialiased">

    <template data-loader-template>
        <x-crudPackage::ui.loader variant="button" />
    </template>

    <div class="flex flex-col lg:flex-row h-full">

        <div class="relative h-[calc(300px+env(safe-area-inset-top))] shrink-0 overflow-hidden lg:h-full lg:w-[640px]">
            @if ($image ?? crudBrand('auth.image'))
                <img
                    src="{{ asset($image ?? crudBrand('auth.image')) }}"
                    alt="{{ crudBrand('name') }}"
                    class="absolute inset-0 w-full h-full object-cover"
                    style="object-position: 60% 40%"
                >
            @endif

            <div class="absolute inset-0" style="background: linear-gradient(180deg, color-mix(in srgb, var(--color-primary) 72%, transparent) 0%, color-mix(in srgb, var(--color-primary) 50%, transparent) 42%, color-mix(in srgb, var(--color-primary) 92%, black 18%) 100%)"></div>

            <div class="absolute -right-[100px] -top-[120px] w-[300px] h-[300px] lg:-right-[190px] lg:-top-[190px] lg:w-[520px] lg:h-[520px] rounded-full border-[3px] border-white/40"></div>
            <div class="absolute -right-[60px] -top-[80px] w-[220px] h-[220px] lg:-right-[150px] lg:-top-[150px] lg:w-[400px] lg:h-[400px] rounded-full border-[3px] border-white/25"></div>

            <div class="absolute left-7 top-[calc(4rem+env(safe-area-inset-top))] lg:left-14 lg:top-14 bg-card rounded-[20px] lg:rounded-card shadow-menu px-5 py-3.5 lg:px-7 lg:py-5">
                <img
                    src="{{ asset(crudBrand('logo.horizontal_light')) }}"
                    alt="{{ crudBrand('name') }}"
                    class="block w-[150px] lg:w-[196px] max-h-[56px] lg:max-h-[72px] object-contain"
                >
            </div>

            <div class="absolute left-7 bottom-10 w-[300px] text-[34px] leading-[1.08] lg:left-14 lg:bottom-[150px] lg:w-[470px] lg:text-[54px] lg:leading-[1.06] font-heading text-white">
                {!! $tagline ?? crudBrand('auth.headline') !!}
            </div>

            <div class="hidden lg:block absolute left-14 bottom-[118px] w-14 h-[5px] bg-accent"></div>

            @if (crudBrand('auth.website') || crudBrand('auth.phone'))
                <div class="hidden lg:flex absolute left-14 bottom-11 w-[528px] h-14 items-center whitespace-nowrap border-t border-white/25 font-label text-sm tracking-[0.14em] text-white/85">
                    <span>{{ crudBrand('auth.website') }}</span>
                    <span class="ml-auto">{{ crudBrand('auth.phone') }}</span>
                </div>
            @endif
        </div>

        <div class="flex-1 min-h-0 flex items-start lg:items-center justify-center px-7 py-8 lg:p-14 overflow-y-auto">
            <div class="w-full max-w-[460px] flex flex-col">
                @if ($back)
                    <a href="{{ $back }}" class="flex items-center gap-3 font-label text-[13px] font-semibold tracking-[0.14em] uppercase text-primary mb-7 hover:opacity-80">
                        <span class="w-[30px] h-[30px] rounded-full border-2 border-primary flex items-center justify-center shrink-0">
                            <x-crudPackage::ui.icon name="chevron-left" :size="16" />
                        </span>
                        Girişe dön
                    </a>
                @endif

                @if ($eyebrow)
                    <div class="font-label text-xs font-semibold tracking-[0.28em] uppercase text-accent">{{ $eyebrow }}</div>
                @endif

                @if ($heading)
                    <h1 class="font-heading text-[30px] lg:text-[46px] leading-[1.1] lg:leading-[1.06] mt-3">{{ $heading }}</h1>
                @endif

                @if ($subtitle)
                    <p class="text-[15px] lg:text-[17px] leading-[1.5] lg:leading-[1.55] text-body mt-1.5 lg:mt-2.5">{{ $subtitle }}</p>
                @endif

                <div class="mt-6 lg:mt-8">
                    {{ $slot }}
                </div>

                @if (crudBrand('privacy_url'))
                    <a href="{{ crudBrand('privacy_url') }}" target="_blank" rel="noopener" class="self-center mt-8 text-sm text-muted hover:text-primary">Gizlilik Politikası</a>
                @endif
            </div>
        </div>

    </div>

    <x-crudPackage::ui.dialog />

</body>
</html>
