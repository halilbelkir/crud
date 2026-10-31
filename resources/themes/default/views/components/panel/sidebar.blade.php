@php
    use crudPackage\Support\Menu;

    $groups = Menu::groups();
    $user   = auth()->user();
@endphp

<aside
    x-data="{ tip: '', tipTop: 0 }"
    data-sidebar
    class="hidden lg:flex shrink-0 m-3 mr-0 flex-col bg-card border border-line rounded-card px-4 py-5 transition-[width] duration-200"
>
    <div class="flex items-center justify-between mb-6" data-menu-head>
        <a href="{{ route('dashboard') }}" class="flex items-center min-w-0">
            <img
                src="{{ asset(crudBrand('logo.horizontal')) }}"
                alt="{{ crudBrand('name') }}"
                class="w-[168px] ml-2"
                data-menu-wide
            >
            <img
                src="{{ asset(crudBrand('logo.mark')) }}"
                alt="{{ crudBrand('name') }}"
                class="w-9 mx-auto"
                data-menu-narrow
            >
        </a>

        <button
            type="button"
            @click="$store.ui.toggleSidebar()"
            class="w-7 h-7 shrink-0 rounded-full border border-line-2 flex items-center justify-center text-muted hover:text-ink hover:border-line-3"
        >
            <x-crudPackage::ui.icon name="chevron-left" :size="14" data-menu-wide />
            <x-crudPackage::ui.icon name="chevron-right" :size="14" data-menu-narrow />
        </button>
    </div>

    <nav class="flex flex-col gap-1 overflow-y-auto" x-init="$store.menu.prime(@js(Menu::collapsedByDefault()))" data-menu-scroll>
        @foreach ($groups as $group)
            @php
                $name = $group['group'];
                $shown = $name
                    ? '$store.menu.open(' . \Illuminate\Support\Js::from($name) . ') || ! $store.ui.sidebar || ' . ($group['active'] ? 'true' : 'false')
                    : 'true';
            @endphp

            @if ($name)
                <button
                    type="button"
                    @click="$store.menu.toggle(@js($name))"
                    class="w-full text-left pl-3.5 pr-2 mb-2 {{ $loop->first ? '' : 'mt-5' }}"
                    data-menu-wide
                >
                    <span class="flex items-center gap-2 font-label text-[10px] font-semibold tracking-[0.2em] uppercase text-faint hover:text-muted">
                        <span>{{ $name }}</span>

                        <x-crudPackage::ui.icon
                            name="chevron-down"
                            :size="12"
                            class="ml-auto shrink-0 transition-transform"
                            ::class="({{ $shown }}) || 'rotate-180'"
                        />
                    </span>
                </button>
            @endif

            <div
                class="flex flex-col gap-[3px]"
                @if ($name)
                    x-show="{{ $shown }}"
                    data-menu-group="{{ $name }}"
                    @if ($group['active']) data-menu-active @endif
                @endif
            >
                @foreach ($group['items'] as $item)
                    @php $active = Menu::isActive($item); @endphp

                    <a
                        href="{{ $item['href'] }}"
                        @if ($item['target']) target="_blank" @endif
                        @mouseenter="if (! $store.ui.sidebar) { tip = @js($item['label']); tipTop = $el.getBoundingClientRect().top + $el.offsetHeight / 2 }"
                        @mouseleave="tip = ''"
                        @class([
                            'h-nav rounded-[23px] flex items-center gap-3.5 px-3.5 text-[15px] transition-colors',
                            'bg-primary text-white font-medium' => $active,
                            'text-body hover:bg-app' => ! $active,
                        ])
                        data-menu-item
                        @if ($active) data-menu-current @endif
                    >
                        <x-crudPackage::ui.icon :name="$item['icon'] ?? 'grid'" class="shrink-0" />

                        <span class="truncate" data-menu-wide>{{ $item['label'] }}</span>

                        @isset($item['badge'])
                            <span
                                class="ml-auto font-label text-[11px] font-semibold text-primary bg-tint-primary rounded-[10px] px-2.5 py-0.5"
                                data-menu-wide
                            >
                                {{ $item['badge'] }}
                            </span>
                        @endisset
                    </a>
                @endforeach
            </div>
        @endforeach
    </nav>

    <div
        x-data="{ open: false }"
        @click.outside="open = false"
        class="relative mt-auto -mx-4 -mb-5 px-4 pt-4 pb-6 border-t border-line flex items-center gap-3"
    >
        <button type="button" @click="open = ! open" class="flex items-center gap-3 w-full min-w-0 text-left" data-menu-item>
            <x-crudPackage::ui.avatar :name="$user?->name ?? ''" :size="40" tone="primary" />

            <span class="min-w-0 flex-1" data-menu-wide>
                <span class="block text-sm font-medium truncate">{{ $user?->name ?? 'Kullanıcı' }}</span>
                <span class="block text-xs text-muted truncate">{{ $user?->email }}</span>
            </span>

            <x-crudPackage::ui.icon name="chevron-down" :size="18" class="shrink-0 text-muted" data-menu-wide />
        </button>

        <div
            x-show="open"
            x-cloak
            x-transition.opacity
            data-menu-panel
            class="absolute left-4 right-4 bottom-[86px] bg-card border border-line-2 rounded-menu shadow-menu p-2 flex flex-col gap-0.5 z-30"
        >
            <a href="{{ route('profile.edit') }}" class="flex items-center gap-3 h-[42px] px-3.5 rounded-[21px] text-body hover:bg-app">
                <x-crudPackage::ui.icon name="user" :size="18" />
                <span class="text-sm">Profilim</span>
            </a>

            @if (crudBrand('privacy_url'))
                <a href="{{ crudBrand('privacy_url') }}" target="_blank" rel="noopener" class="flex items-center gap-3 h-[42px] px-3.5 rounded-[21px] text-body hover:bg-app">
                    <x-crudPackage::ui.icon name="lock" :size="18" />
                    <span class="text-sm">Gizlilik Politikası</span>
                </a>
            @endif

            <div class="h-px bg-line-soft mx-1 my-1.5"></div>

            <a href="{{ route('logout') }}" class="w-full flex items-center gap-3 h-[42px] px-3.5 rounded-[21px] text-primary hover:bg-tint-primary">
                <x-crudPackage::ui.icon name="logout" :size="18" />
                <span class="text-sm">Çıkış yap</span>
            </a>
        </div>
    </div>

    <div
        x-show="tip && ! $store.ui.sidebar"
        x-cloak
        x-transition.opacity.duration.150ms
        :style="`top: ${tipTop}px`"
        x-text="tip"
        class="fixed left-[112px] -translate-y-1/2 z-40 h-9 flex items-center whitespace-nowrap bg-card border border-line-2 rounded-field shadow-menu px-4 text-sm text-ink pointer-events-none"
    ></div>
</aside>
