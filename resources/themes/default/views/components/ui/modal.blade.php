@props([
    'name',
    'title'    => null,
    'subtitle' => null,
    'maxWidth' => 'max-w-[560px]',
    'confirmClose' => false,
    'center'   => false,
    'locked'   => false,
    'screen'   => false,
])

<div
    x-data="{
        open: {{ $locked ? 'true' : 'false' }},
        locked: {{ $locked ? 'true' : 'false' }},
        confirmClose: {{ $confirmClose ? 'true' : 'false' }},
        requestClose()
        {
            if (this.locked)
            {
                return;
            }

            if (! this.confirmClose)
            {
                this.open = false;

                return;
            }

            $store.dialog.ask({
                title:        'Vazgeçilsin mi?',
                message:      'Bu pencereyi kapatırsanız yaptığınız seçimler kaydedilmeden kaybolur.',
                tone:         'warning',
                confirmLabel: 'Evet, vazgeç',
                action:       () => this.open = false,
            });
        },
    }"
    x-init="$watch('open', value => value || $dispatch('modal-closed', '{{ $name }}'))"
    @modal-open.window="if ($event.detail === '{{ $name }}') open = true"
    @modal-close.window="if ($event.detail === '{{ $name }}') open = false"
    @keydown.escape.window="if (open) requestClose()"
    x-effect="$store.overlay.set('modal-{{ $name }}', open)"
    x-show="open"
    x-transition.opacity.duration.150ms
    x-cloak
    @class(['fixed inset-0 z-50 flex items-center justify-center', 'p-4' => ! $screen, 'p-0 lg:p-4' => $screen])
>
    <div x-show="open" x-transition.opacity @click="requestClose()" class="absolute inset-0 bg-ink/40"></div>

    <div
        x-show="open"
        x-transition:enter="transition duration-200"
        x-transition:enter-start="opacity-0 scale-95"
        x-transition:enter-end="opacity-100 scale-100"
        x-transition:leave="transition duration-150"
        x-transition:leave-start="opacity-100 scale-100"
        x-transition:leave-end="opacity-0 scale-95"
        @class([
            'relative w-full bg-card shadow-modal flex flex-col',
            'rounded-modal max-h-[88vh] p-5 lg:p-8' => ! $screen,
            'h-[100dvh] rounded-none p-3 lg:h-[92vh] lg:rounded-modal lg:p-5' => $screen,
            $maxWidth,
        ])
    >
        <div @class(['shrink-0 flex items-start gap-4', 'relative' => $center])>
            @isset($header)
                {{ $header }}
            @else
                <div class="min-w-0 flex-1">
                    @if ($title)
                        <h2 class="font-heading text-[26px] leading-tight">{{ $title }}</h2>
                    @endif

                    @if ($subtitle)
                        <p class="text-sm text-muted mt-1.5">{{ $subtitle }}</p>
                    @endif
                </div>
            @endisset

            <button type="button" x-show="! locked" @click="requestClose()" @class([
                'w-9 h-9 shrink-0 rounded-full border border-line flex items-center justify-center text-muted hover:text-ink',
                'absolute top-0 right-0' => $center,
            ])>
                <x-crudPackage::ui.icon name="close" :size="16" />
            </button>
        </div>

        <div @class(['flex-1 min-h-0', 'mt-6 overflow-y-auto overscroll-contain' => ! $screen, 'mt-3 flex flex-col' => $screen])>
            {{ $slot }}
        </div>

        @isset($footer)
            <div class="mt-8 shrink-0 flex flex-wrap items-center justify-end gap-3">
                {{ $footer }}
            </div>
        @endisset
    </div>
</div>
