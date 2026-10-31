@props([
    'name',
    'title'       => null,
    'subtitle'    => null,
    'width'       => 'lg:w-[480px]',
    'confirmClose' => false,
])

<div
    x-data="{
        open: false,

        requestClose()
        {
            if (! this.open)
            {
                return false;
            }

            @if ($confirmClose)
                $store.dialog.ask(
                {
                    title:        'Vazgeçilsin mi?',
                    message:      'Girdiğiniz bilgiler kaydedilmeyecek.',
                    tone:         'warning',
                    confirmLabel: 'Evet, vazgeç',
                    action:       () => { this.open = false },
                });

                return false;
            @else
                this.open = false;

                return true;
            @endif
        },
    }"
    @drawer-open.window="if ($event.detail === '{{ $name }}') open = true"
    @drawer-close.window="if ($event.detail === '{{ $name }}') open = false"
    @keydown.escape.window="requestClose()"
    x-effect="$store.overlay.set('drawer-{{ $name }}', open)"
    x-show="open"
    x-transition.opacity.duration.150ms
    x-cloak
    class="fixed inset-0 z-50"
>
    <div x-show="open" x-transition.opacity @click="requestClose()" class="absolute inset-0 bg-ink/40"></div>

    <div
        x-show="open"
        x-init="window.initSheetDrag($el, () => requestClose())"
        x-transition:enter="transition duration-200"
        x-transition:enter-start="translate-y-full lg:translate-y-0 lg:translate-x-full"
        x-transition:enter-end="translate-y-0 lg:translate-x-0"
        x-transition:leave="transition duration-150"
        x-transition:leave-start="translate-y-0 lg:translate-x-0"
        x-transition:leave-end="translate-y-full lg:translate-y-0 lg:translate-x-full"
        @class([
            'absolute inset-x-0 bottom-0 max-h-[88vh] rounded-t-[40px] shadow-[0_-18px_50px_rgba(0,0,0,0.22)] lg:inset-x-auto lg:right-3.5 lg:top-3.5 lg:bottom-3.5 lg:max-h-none lg:rounded-[40px] lg:shadow-[-24px_0_60px_rgba(0,0,0,0.2)] bg-card overflow-hidden flex flex-col',
            $width,
        ])
    >
        <div class="lg:hidden shrink-0 pt-3 pb-1.5 flex justify-center">
            <span class="w-12 h-[5px] rounded-[3px] bg-line-3"></span>
        </div>

        <div class="flex items-center gap-3.5 px-5 pt-1.5 pb-3.5 lg:px-8 lg:pt-[26px] lg:pb-5 border-b border-line-soft shrink-0">
            <div class="min-w-0 flex-1">
                @if ($title)
                    <h2 class="font-heading text-[21px] lg:text-[26px] leading-[1.1]">{{ $title }}</h2>
                @endif

                @if ($subtitle)
                    <p class="text-[13px] text-muted mt-1">{{ $subtitle }}</p>
                @endif
            </div>

            <button type="button" @click="requestClose()" class="w-8 h-8 lg:w-[38px] lg:h-[38px] shrink-0 rounded-full border border-line flex items-center justify-center text-body hover:text-ink">
                <x-crudPackage::ui.icon name="close" :size="16" />
            </button>
        </div>

        <div class="flex-1 min-h-0 overflow-y-auto overscroll-contain px-5 py-4 lg:px-8 lg:py-[26px]">
            {{ $slot }}
        </div>

        @isset($footer)
            <div class="shrink-0 px-5 py-4 lg:px-8 lg:py-5 border-t border-line-soft flex items-center gap-3">
                {{ $footer }}
            </div>
        @endisset
    </div>
</div>
