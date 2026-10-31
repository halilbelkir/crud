<div
    x-data
    x-cloak
    class="fixed top-5 right-5 z-[70] flex flex-col gap-3 w-[420px] max-w-[calc(100vw-2.5rem)]"
>
    <template x-for="item in $store.toast.items" :key="item.id">
        <div
            x-transition:enter="transition duration-200"
            x-transition:enter-start="opacity-0 translate-x-6"
            x-transition:enter-end="opacity-100 translate-x-0"
            class="relative overflow-hidden rounded-[20px] flex items-center gap-3.5 px-[18px] py-3.5 shadow-menu"
            :class="{
                'bg-success text-white': item.tone === 'success',
                'bg-warning text-on-warning': item.tone === 'warning',
                'bg-danger text-white': item.tone === 'danger',
                'bg-ink text-white': item.tone === 'info',
            }"
        >
            <span class="w-9 h-9 shrink-0 rounded-full border-2 border-current/70 flex items-center justify-center">
                <span x-show="item.tone === 'success'"><x-crudPackage::ui.icon name="check" :size="17" :stroke="2.6" /></span>
                <span x-show="item.tone === 'warning'" class="font-label text-sm font-semibold">!</span>
                <span x-show="item.tone === 'danger'"><x-crudPackage::ui.icon name="close" :size="17" :stroke="2.6" /></span>
                <span x-show="item.tone === 'info'" class="font-label text-sm font-semibold">i</span>
            </span>

            <div class="flex-1 min-w-0 text-sm leading-snug">
                <strong x-show="item.lead" x-text="item.lead"></strong>
                <span x-text="item.message"></span>
            </div>

            <button type="button" @click="$store.toast.close(item.id)" class="shrink-0 opacity-70 hover:opacity-100">
                <x-crudPackage::ui.icon name="close" :size="16" />
            </button>

            <span
                class="toast-timer absolute left-0 bottom-0 h-[3px] bg-current/45"
                @animationend="$store.toast.close(item.id)"
            ></span>
        </div>
    </template>
</div>
