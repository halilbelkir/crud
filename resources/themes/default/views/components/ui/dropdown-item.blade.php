@props([
    'href'     => null,
    'icon'     => null,
    'danger'   => false,
    'active'   => false,
    'method'   => null,
    'drawer'   => false,
    'title'    => null,
    'confirm'  => null,
    'payload'  => [],
    'disabled' => false,
    'copy'      => null,
    'copied'    => 'Kopyalandı',
])

@php
    $classes = implode(' ', array_filter([
        'flex items-center gap-3 h-[42px] px-3.5 rounded-[21px] text-sm whitespace-nowrap w-full',
        $disabled ? 'text-disabled cursor-not-allowed' : ($danger ? 'text-danger hover:bg-tint-danger' : 'text-body hover:bg-app'),
        $active ? 'bg-app text-ink' : null,
    ]));
@endphp

@if ($disabled)
    <span {{ $attributes->class($classes) }}>
        @if ($icon)
            <x-crudPackage::ui.icon :name="$icon" :size="17" class="shrink-0" />
        @endif

        {{ $slot }}
    </span>
@else

@if ($drawer && $href)
    <button
        type="button"
        x-data
        @click="$dispatch('form-drawer', { url: {{ \Illuminate\Support\Js::from($href) }}, title: {{ \Illuminate\Support\Js::from($title ?? trim($slot->toHtml())) }} })"
        {{ $attributes->class($classes) }}
    >
        @if ($icon)
            <x-crudPackage::ui.icon :name="$icon" :size="17" class="shrink-0" />
        @endif

        {{ $slot }}
    </button>
@elseif ($method && $href)
    <form method="POST" action="{{ $href }}">
        @csrf
        @if ($method !== 'POST')
            @method($method)
        @endif

        @foreach ($payload as $key => $value)
            <input type="hidden" name="{{ $key }}" value="{{ $value }}">
        @endforeach

        @if ($confirm)
            <button
                type="button"
                x-data
                @click="$store.dialog.ask({
                    title: {{ \Illuminate\Support\Js::from($confirm['title'] ?? 'Emin misiniz?') }},
                    message: {{ \Illuminate\Support\Js::from($confirm['message'] ?? '') }},
                    tone: {{ \Illuminate\Support\Js::from($confirm['tone'] ?? 'danger') }},
                    confirmLabel: {{ \Illuminate\Support\Js::from($confirm['confirmLabel'] ?? 'Evet, sil') }},
                    verify: {{ \Illuminate\Support\Js::from($confirm['verify'] ?? null) }},
                    choices: {{ \Illuminate\Support\Js::from($confirm['choices'] ?? null) }},
                    choiceName: {{ \Illuminate\Support\Js::from($confirm['choiceName'] ?? null) }},
                    choiceLabel: {{ \Illuminate\Support\Js::from($confirm['choiceLabel'] ?? '') }},
                    action: () => window.submitForm($el.closest('form'), $store.dialog.extra()),
                })"
                {{ $attributes->class($classes) }}
            >
                @if ($icon)
                    <x-crudPackage::ui.icon :name="$icon" :size="17" class="shrink-0" />
                @endif

                {{ $slot }}
            </button>
        @else
            <button type="button" x-data @click="window.submitForm($el.closest('form'))" {{ $attributes->class($classes) }}>
                @if ($icon)
                    <x-crudPackage::ui.icon :name="$icon" :size="17" class="shrink-0" />
                @endif

                {{ $slot }}
            </button>
        @endif
    </form>
@elseif ($copy !== null)
    <button
        type="button"
        x-data
        @click="navigator.clipboard?.writeText({{ \Illuminate\Support\Js::from($copy) }}); $store.toast.show({{ \Illuminate\Support\Js::from($copied) }}); open = false"
        {{ $attributes->class($classes) }}
    >
        @if ($icon)
            <x-crudPackage::ui.icon :name="$icon" :size="17" class="shrink-0" />
        @endif

        {{ $slot }}
    </button>
@elseif ($href)
    <a href="{{ $href }}" {{ $attributes->class($classes) }}>
        @if ($icon)
            <x-crudPackage::ui.icon :name="$icon" :size="17" class="shrink-0" />
        @endif

        {{ $slot }}
    </a>
@else
    <button type="button" {{ $attributes->class($classes) }}>
        @if ($icon)
            <x-crudPackage::ui.icon :name="$icon" :size="17" class="shrink-0" />
        @endif

        {{ $slot }}
    </button>
@endif
@endif
