@php
    use crudPackage\Support\Menu;

    $groups  = Menu::groups();
    $primary = Menu::primary(4);
@endphp

<div class="lg:hidden shrink-0 px-4 pb-[max(1rem,env(safe-area-inset-bottom))]" x-data x-effect="$store.overlay.set('mobile-menu', $store.ui.mobileMenu)">
    <nav class="h-[68px] rounded-[34px] bg-card border border-line shadow-[0_12px_30px_rgba(0,0,0,0.14)] flex items-center justify-between px-2.5">
        @foreach ($primary as $item)
            @php $active = Menu::isActive($item); @endphp

            <a
                href="{{ $item['href'] }}"
                @if ($item['target']) target="_blank" @endif
                @class([
                    'flex items-center justify-center',
                    'h-12 rounded-3xl bg-primary text-white flex-row gap-[9px] px-[18px] min-w-0' => $active,
                    'min-w-[54px] px-2 flex-col gap-[3px] text-muted' => ! $active,
                ])
            >
                <x-crudPackage::ui.icon :name="$item['icon'] ?? 'grid'" :size="22" class="shrink-0" />

                <span @class([
                    'font-label whitespace-nowrap truncate',
                    'text-[11px] font-semibold tracking-[0.06em]' => $active,
                    'text-[10px] font-medium' => ! $active,
                ])>{{ $item['label'] }}</span>
            </a>
        @endforeach

        <button type="button" @click="$store.ui.openMobileMenu()" class="min-w-[54px] px-2 flex flex-col items-center justify-center gap-[3px] text-muted">
            <x-crudPackage::ui.icon name="menu" :size="22" />
            <span class="font-label text-[10px] font-medium">Menü</span>
        </button>
    </nav>

    <div
        x-show="$store.ui.mobileMenu"
        x-cloak
        @click="$store.ui.closeMobileMenu()"
        x-transition.opacity
        class="fixed inset-0 bg-ink/40 z-40"
    ></div>

    <div
        x-show="$store.ui.mobileMenu"
        x-cloak
        x-transition:enter="transition duration-200"
        x-transition:enter-start="translate-y-full"
        x-transition:enter-end="translate-y-0"
        x-transition:leave="transition duration-150"
        x-transition:leave-start="translate-y-0"
        x-transition:leave-end="translate-y-full"
        x-effect="$store.ui.mobileMenu && $nextTick(() => window.revealMenuCurrent($el.parentElement))"
        x-init="window.initSheetDrag($el, () => $store.ui.closeMobileMenu())"
        data-menu-scroll
        class="fixed inset-x-0 bottom-0 z-50 bg-card rounded-t-modal max-h-[80vh] overflow-y-auto overscroll-contain px-5 pt-4 pb-8"
    >
        <div class="-mx-5 -mt-4 pt-3 pb-1.5 mb-3.5 flex justify-center">
            <span class="w-12 h-[5px] rounded-[3px] bg-line-3"></span>
        </div>

        @foreach ($groups as $group)
            @if ($group['group'])
                <div class="font-label text-[10px] font-semibold tracking-[0.2em] uppercase text-faint ml-3.5 mb-2 {{ $loop->first ? '' : 'mt-5' }}">
                    {{ $group['group'] }}
                </div>
            @endif

            <div class="flex flex-col gap-[3px]">
                @foreach ($group['items'] as $item)
                    @php $active = Menu::isActive($item); @endphp

                    <a
                        href="{{ $item['href'] }}"
                        @if ($item['target']) target="_blank" @endif
                        @class([
                            'h-nav rounded-[23px] flex items-center gap-3.5 px-3.5 text-[15px]',
                            'bg-primary text-white font-medium' => $active,
                            'text-body' => ! $active,
                        ])
                        @if ($active) data-menu-current @endif
                    >
                        <x-crudPackage::ui.icon :name="$item['icon'] ?? 'grid'" class="shrink-0" />
                        <span class="truncate">{{ $item['label'] }}</span>
                    </a>
                @endforeach
            </div>
        @endforeach

        <div class="h-px bg-line-soft my-4"></div>

        <a href="{{ route('logout') }}" class="w-full h-nav rounded-[23px] flex items-center gap-3.5 px-3.5 text-primary">
            <x-crudPackage::ui.icon name="logout" />
            <span class="text-[15px]">Çıkış yap</span>
        </a>

        @if (crudBrand('privacy_url'))
            <a href="{{ crudBrand('privacy_url') }}" target="_blank" rel="noopener" class="block text-center mt-3 text-xs text-muted">Gizlilik Politikası</a>
        @endif
    </div>
</div>
