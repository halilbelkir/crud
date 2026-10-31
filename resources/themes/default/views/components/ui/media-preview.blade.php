<div x-data="mediaPreview" @media-preview.window="show($event.detail)">
    <x-crudPackage::ui.modal name="media-preview" max-width="max-w-[1100px]" screen>
        <x-slot:header>
            <div class="min-w-0 flex-1">
                <h2 class="font-heading text-[20px] lg:text-[24px] leading-tight truncate" x-text="current?.name"></h2>
                <p class="text-sm text-muted mt-1" x-text="current ? (index + 1) + ' / ' + items.length + ' · ' + current.extension.toUpperCase() : ''"></p>
            </div>
        </x-slot:header>

        <div
            data-media-preview
            @keydown.left.window="$el.offsetParent && move(-1)"
            @keydown.right.window="$el.offsetParent && move(1)"
            class="relative flex-1 min-h-0 rounded-[20px] border border-line-soft bg-subtle flex items-center justify-center overflow-hidden"
        >
            <template x-if="current?.kind === 'image'">
                <img :src="current.url" :alt="current.name" class="max-w-full max-h-full object-contain">
            </template>

            <template x-if="current?.kind === 'pdf'">
                <iframe :src="current.url" class="w-full h-full bg-card"></iframe>
            </template>

            <template x-if="current?.kind === 'video'">
                <video :src="current.url" controls class="max-w-full max-h-full"></video>
            </template>

            <template x-if="current?.kind === 'audio'">
                <audio :src="current.url" controls class="w-[min(420px,90%)]"></audio>
            </template>

            <template x-if="current?.kind === 'file'">
                <x-crudPackage::ui.empty-state icon="file" title="Önizleme yok" message="Bu dosya türü tarayıcıda gösterilemiyor. Dosyayı indirerek açabilirsiniz." />
            </template>

            <template x-if="items.length > 1">
                <div>
                    <button type="button" @click="move(-1)" class="absolute left-3 top-1/2 -translate-y-1/2 w-10 h-10 rounded-full bg-card border border-line shadow-menu flex items-center justify-center text-muted hover:text-ink">
                        <x-crudPackage::ui.icon name="chevron-left" :size="18" />
                    </button>

                    <button type="button" @click="move(1)" class="absolute right-3 top-1/2 -translate-y-1/2 w-10 h-10 rounded-full bg-card border border-line shadow-menu flex items-center justify-center text-muted hover:text-ink">
                        <x-crudPackage::ui.icon name="chevron-right" :size="18" />
                    </button>
                </div>
            </template>
        </div>

        <div class="shrink-0 flex flex-wrap items-center justify-end gap-2.5 pt-3">
            <x-crudPackage::ui.button variant="outline" size="sm" icon="copy" @click="window.copyText(current.url, 'Bağlantı kopyalandı')">Bağlantıyı kopyala</x-crudPackage::ui.button>
            <x-crudPackage::ui.button href="#" variant="outline" size="sm" icon="link" target="_blank" x-bind:href="current?.url">Yeni sekmede aç</x-crudPackage::ui.button>
            <x-crudPackage::ui.button href="#" size="sm" icon="export" x-show="current?.download" x-bind:href="current?.download">İndir</x-crudPackage::ui.button>
        </div>
    </x-crudPackage::ui.modal>
</div>
