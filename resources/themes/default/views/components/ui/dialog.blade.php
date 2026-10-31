@php
    $status = session('status');
    $error  = session('error');
@endphp

<div
    x-data
    @keydown.escape.window.capture="$store.dialog.handleEscape($event)"
    x-effect="$store.overlay.set('dialog', $store.dialog.confirm.open || $store.dialog.result.open)"
>

    <div
        x-show="$store.dialog.confirm.open"
        x-transition.opacity.duration.150ms
        x-cloak
        class="fixed inset-0 z-[60] flex items-center justify-center p-4"
    >
        <div x-show="$store.dialog.confirm.open" x-transition.opacity @click="$store.dialog.cancel()" class="absolute inset-0 bg-ink/45"></div>

        <div
            x-show="$store.dialog.confirm.open"
            x-transition:enter="transition duration-200"
            x-transition:enter-start="opacity-0 scale-95"
            x-transition:enter-end="opacity-100 scale-100"
            x-transition:leave="transition duration-150"
            x-transition:leave-start="opacity-100 scale-100"
            x-transition:leave-end="opacity-0 scale-95"
            class="relative w-full max-w-[460px] bg-card rounded-modal shadow-modal p-7 flex flex-col gap-3.5"
        >
            <div class="relative w-16 h-16">
                <span
                    class="absolute inset-0 rounded-full border-[3px]"
                    :class="{ danger: 'border-tint-danger-strong', warning: 'border-line-warning' }[$store.dialog.confirm.tone] ?? 'border-tint-accent-strong'"
                ></span>

                <span
                    class="absolute inset-[9px] rounded-full flex items-center justify-center"
                    :class="{ danger: 'bg-tint-danger text-danger', warning: 'bg-tint-warning text-on-tint-warning' }[$store.dialog.confirm.tone] ?? 'bg-tint-accent-2 text-on-tint-accent'"
                >
                    <span x-show="$store.dialog.confirm.tone === 'danger' && $store.dialog.confirm.icon !== 'alert'">
                        <x-crudPackage::ui.icon name="trash" :size="20" />
                    </span>

                    <span x-show="$store.dialog.confirm.tone !== 'danger' || $store.dialog.confirm.icon === 'alert'" x-cloak>
                        <x-crudPackage::ui.icon name="alert" :size="20" />
                    </span>
                </span>
            </div>

            <div>
                <div class="font-heading text-2xl leading-[1.15]" x-text="$store.dialog.confirm.title"></div>
                <div class="text-sm leading-normal text-body mt-2 [overflow-wrap:anywhere]" x-text="$store.dialog.confirm.message" x-show="$store.dialog.confirm.message"></div>
            </div>

            <div x-show="$store.dialog.confirm.choices" x-cloak class="flex flex-col gap-[7px]">
                <span class="label-caps" x-text="$store.dialog.confirm.choiceLabel"></span>

                <div class="h-btn rounded-3xl border border-line-2 bg-subtle flex items-center px-5 focus-within:border-primary">
                    <select
                        x-model="$store.dialog.confirm.choice"
                        @keydown.enter.prevent="$store.dialog.accept()"
                        class="flex-1 min-w-0 bg-transparent text-[15px] text-ink outline-none appearance-none cursor-pointer"
                    >
                        <option value="">Seçin</option>

                        <template x-for="(label, value) in ($store.dialog.confirm.choices ?? {})" :key="value">
                            <option :value="value" x-text="label"></option>
                        </template>
                    </select>

                    <x-crudPackage::ui.icon name="chevron-down" :size="16" class="shrink-0 text-muted" />
                </div>
            </div>

            <div x-show="$store.dialog.confirm.prompt" x-cloak class="flex flex-col gap-[7px]">
                <img
                    x-show="$store.dialog.confirm.image"
                    :src="$store.dialog.confirm.image"
                    :alt="$store.dialog.confirm.prompt"
                    class="w-full max-w-[368px] rounded-xl border border-line-2 bg-white"
                >

                <span class="label-caps" x-text="$store.dialog.confirm.prompt"></span>

                <input
                    type="text"
                    x-model="$store.dialog.confirm.value"
                    @keydown.enter.prevent="$store.dialog.accept()"
                    autocomplete="off"
                    class="h-btn rounded-3xl border border-line-2 bg-subtle px-5 text-[15px] text-ink placeholder:text-faint outline-none focus:border-primary"
                >
            </div>

            <div x-show="$store.dialog.confirm.verify" x-cloak class="flex flex-col gap-[7px]">
                <span class="label-caps" x-text="'Onaylamak için ' + ($store.dialog.confirm.verify ?? '') + ' yazın'"></span>

                <input
                    type="text"
                    x-model="$store.dialog.confirm.typed"
                    @keydown.enter.prevent="$store.dialog.accept()"
                    :placeholder="$store.dialog.confirm.verify"
                    class="h-btn rounded-3xl border border-line-2 bg-subtle px-5 text-[15px] text-ink placeholder:text-faint outline-none focus:border-primary"
                >
            </div>

            <div class="flex gap-2.5 mt-1">
                <button
                    type="button"
                    @click="$store.dialog.escape()"
                    class="flex-1 h-btn rounded-3xl border border-line-2 flex items-center justify-center font-label text-xs font-semibold tracking-[0.1em] uppercase text-body hover:border-line-3"
                    x-text="$store.dialog.confirm.busy ? 'Sayfayı yenile' : $store.dialog.confirm.cancelLabel"
                ></button>

                <button
                    type="button"
                    @click="$store.dialog.accept($el)"
                    :disabled="! $store.dialog.verified() || $store.dialog.confirm.busy"
                    class="h-btn rounded-3xl flex items-center justify-center font-label text-xs font-semibold tracking-[0.1em] uppercase"
                    :class="[
                        $store.dialog.confirm.verify || $store.dialog.confirm.choices || $store.dialog.confirm.prompt ? 'flex-[1.3]' : 'flex-1',
                        $store.dialog.verified()
                            ? ({ danger: 'bg-danger text-white hover:opacity-90', warning: 'bg-warning text-on-warning hover:opacity-90' }[$store.dialog.confirm.tone] ?? 'bg-accent text-white hover:opacity-90')
                            : 'bg-line-soft text-disabled cursor-not-allowed',
                    ]"
                    x-text="$store.dialog.confirm.confirmLabel"
                ></button>
            </div>
        </div>
    </div>

    <div
        x-show="$store.dialog.result.open"
        x-transition.opacity.duration.150ms
        x-cloak
        class="fixed inset-0 z-[60] flex items-center justify-center p-4"
    >
        <div x-show="$store.dialog.result.open" x-transition.opacity @click="$store.dialog.dismiss()" class="absolute inset-0 bg-ink/45"></div>

        <div
            x-show="$store.dialog.result.open"
            x-transition:enter="transition duration-200"
            x-transition:enter-start="opacity-0 scale-95"
            x-transition:enter-end="opacity-100 scale-100"
            x-transition:leave="transition duration-150"
            x-transition:leave-start="opacity-100 scale-100"
            x-transition:leave-end="opacity-0 scale-95"
            class="relative w-full max-w-[440px] bg-card rounded-modal shadow-modal p-7 flex flex-col items-center text-center gap-4"
        >
            <div class="relative w-20 h-20">
                <svg viewBox="0 0 64 64" class="absolute inset-0 w-20 h-20 -rotate-90">
                    <circle cx="32" cy="32" r="29" fill="none" stroke="var(--color-line)" stroke-width="3" />
                    <circle
                        cx="32"
                        cy="32"
                        r="29"
                        fill="none"
                        stroke-width="3"
                        stroke-linecap="round"
                        class="countdown"
                        :stroke="$store.dialog.result.tone === 'success' ? 'var(--color-success)' : 'var(--color-danger)'"
                        @animationend="$store.dialog.dismiss()"
                    />
                </svg>

                <span
                    class="absolute inset-[11px] rounded-full flex items-center justify-center text-white"
                    :class="$store.dialog.result.tone === 'success' ? 'bg-success' : 'bg-danger'"
                >
                    <span x-show="$store.dialog.result.tone === 'success'">
                        <x-crudPackage::ui.icon name="check" :size="26" :stroke="2.6" />
                    </span>

                    <span x-show="$store.dialog.result.tone !== 'success'" x-cloak>
                        <x-crudPackage::ui.icon name="close" :size="26" :stroke="2.6" />
                    </span>
                </span>
            </div>

            <div>
                <div class="font-heading text-2xl leading-[1.15]" x-text="$store.dialog.result.title"></div>
                <div class="text-sm leading-normal text-body mt-2 [overflow-wrap:anywhere]" x-show="$store.dialog.result.message" x-text="$store.dialog.result.message"></div>
            </div>

            <button
                type="button"
                @click="$store.dialog.dismiss()"
                class="w-full h-btn rounded-3xl border border-line-2 flex items-center justify-center font-label text-xs font-semibold tracking-[0.1em] uppercase text-body hover:border-line-3"
            >
                Şimdi kapat
            </button>
        </div>
    </div>

    @if ($status)
        <div x-init="$store.dialog.notify(@js($status))"></div>
    @elseif ($error)
        <div x-init="$store.dialog.notify('İşlem tamamlanamadı', @js($error), 'danger')"></div>
    @endif

</div>
