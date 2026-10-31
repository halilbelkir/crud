<div
    x-data="formDrawer"
    data-form-drawer
    @form-drawer.window="load($event.detail.url, $event.detail.title)"
    @form-drawer-close.window="close()"
    @keydown.escape.window="requestClose()"
    @submit="submit($event)"
    x-effect="$store.overlay.set('form-drawer', open)"
    x-show="open"
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
        class="absolute inset-x-0 bottom-0 max-h-[88vh] rounded-t-[40px] shadow-[0_-18px_50px_rgba(0,0,0,0.22)] lg:inset-x-auto lg:right-3.5 lg:top-3.5 lg:bottom-3.5 lg:w-[720px] lg:max-h-none lg:rounded-[40px] lg:shadow-[-24px_0_60px_rgba(0,0,0,0.2)] bg-card overflow-hidden flex flex-col"
    >
        <div class="lg:hidden shrink-0 pt-3 pb-1.5 flex justify-center">
            <span class="w-12 h-[5px] rounded-[3px] bg-line-3"></span>
        </div>

        <div class="flex items-center gap-3.5 px-5 pt-1.5 pb-3.5 lg:px-8 lg:pt-[26px] lg:pb-5 border-b border-line-soft shrink-0">
            <h2 class="font-heading text-[21px] lg:text-[26px] leading-[1.1] flex-1 min-w-0" x-text="title"></h2>

            <button type="button" @click="requestClose()" class="w-8 h-8 lg:w-[38px] lg:h-[38px] shrink-0 rounded-full border border-line flex items-center justify-center text-body hover:text-ink">
                <x-crudPackage::ui.icon name="close" :size="16" />
            </button>
        </div>

        <div class="flex-1 min-h-0 overflow-y-auto overscroll-contain px-5 py-4 lg:px-8 lg:py-[26px]" x-ref="body">
            <div x-show="loading" class="flex items-center justify-center py-16">
                <x-crudPackage::ui.loader />
            </div>

            <div x-show="! loading" x-html="html" :data-refresh-source="url"></div>
        </div>
    </div>
</div>
