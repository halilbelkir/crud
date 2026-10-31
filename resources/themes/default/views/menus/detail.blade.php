<x-crudPackage::layouts.app
    :title="$menu->title"
    :breadcrumb="[['label' => 'Menüler', 'url' => route('menus.index')], ['label' => $menu->title]]"
>
    @php
        $meta = [
            $items->count() . ' öğe',
            $menu->created_at ? $menu->created_at->translatedFormat('d.m.Y') . ' tarihinden beri' : null,
        ];
    @endphp

    <div class="flex flex-col gap-3.5">

        <div data-refresh="detail-header" class="contents">
        <x-crudPackage::ui.detail-header
            :title="$menu->title"
            :status="$menu->status == 1 ? 'Aktif' : 'Pasif'"
            :status-tone="$menu->status == 1 ? 'success' : 'muted'"
            :meta="$meta"
        >
            <x-slot:actions>
                @if (\crudPackage\Support\Permissions::allows('menus.edit'))
                    <x-crudPackage::ui.button
                        x-data
                        @click="$dispatch('form-drawer', { url: {{ \Illuminate\Support\Js::from(route('menus.edit', $menu)) }}, title: 'Menüyü düzenle' })"
                        variant="outline"
                        size="sm"
                        icon="pen"
                    >
                        Düzenle
                    </x-crudPackage::ui.button>
                @endif
            </x-slot:actions>
        </x-crudPackage::ui.detail-header>
        </div>

        @php
            $create = \crudPackage\Support\Permissions::allows('menus.items.create') ? ['label' => 'Yeni öğe', 'href' => route('menus.items.create', $menu)] : null;
            $order  = \crudPackage\Support\Permissions::allows('menus.items.updateOrder') ? route('menus.items.updateOrder', $menu) : null;
        @endphp

        <div class="flex flex-col gap-2.5 min-h-0 lg:contents">
            <div class="lg:hidden flex items-end gap-3 shrink-0">
                <div class="min-w-0">
                    <div class="font-heading text-[22px] leading-[1.1] truncate">Menü öğeleri</div>
                    <div class="text-[12.5px] text-muted mt-0.5 truncate">Sürükleyerek sıralayın, sağa çekerek alt öğe yapın</div>
                </div>

                @if ($create)
                    <span class="ml-auto shrink-0 flex items-center gap-2">
                        <x-crudPackage::ui.datatable.create :create="$create" compact />
                    </span>
                @endif
            </div>

            <div class="bg-card border border-line rounded-card flex flex-col">
                @if ($create)
                    <div class="hidden lg:flex px-5 py-4 border-b border-line-soft items-center gap-3">
                        <span class="text-sm text-muted">Sürükleyerek sıralayın, sağa çekerek alt öğe yapın</span>

                        <div class="ml-auto flex items-center gap-2.5 flex-none">
                            <x-crudPackage::ui.datatable.create :create="$create" />
                        </div>
                    </div>
                @endif

                @if ($items->isNotEmpty())
                    <div class="hidden lg:grid grid-cols-[44px_1fr_160px_60px] items-center gap-x-6 px-5 h-[46px] bg-subtle border-b border-line-soft label-caps text-[11px]">
                        <span></span>
                        <span>Öğe</span>
                        <span>Tür</span>
                        <span></span>
                    </div>

                    <div data-menu-tree @if ($order) x-data x-init="window.initMenuTree($el, @js($order))" @endif>
                        @foreach ($tree as $node)
                            <div data-node="{{ $node['item']->id }}">
                                @include('crudPackage::menus.items.row', ['item' => $node['item'], 'draggable' => (bool) $order])

                                <div data-children>
                                    @foreach ($node['children'] as $child)
                                        <div data-node="{{ $child->id }}">
                                            @include('crudPackage::menus.items.row', ['item' => $child, 'draggable' => (bool) $order])
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endforeach
                    </div>

                    @if ($fixed->isNotEmpty())
                        <div class="px-3.5 lg:px-5 h-[46px] flex items-center bg-subtle border-b border-line-soft label-caps text-[11px]">Sistem öğeleri</div>

                        @foreach ($fixed as $item)
                            @include('crudPackage::menus.items.row', ['item' => $item, 'draggable' => false])
                        @endforeach
                    @endif
                @else
                    <x-crudPackage::ui.empty-state title="Öğe yok" message="Bu menüde henüz öğe yok. Yeni öğe ekleyerek başlayın." />
                @endif
            </div>
        </div>

    </div>
</x-crudPackage::layouts.app>
