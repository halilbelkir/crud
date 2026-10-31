<div data-node-row class="grid grid-cols-[28px_1fr_60px] lg:grid-cols-[44px_1fr_160px_60px] items-center gap-x-3 lg:gap-x-6 px-3.5 lg:px-5 min-h-16 text-sm border-b border-line-softer hover:bg-subtle">
    @if ($draggable)
        <span data-drag class="flex items-center text-disabled cursor-grab active:cursor-grabbing touch-none">
            <x-crudPackage::ui.icon name="grip" :size="18" />
        </span>
    @else
        <span></span>
    @endif

    <span data-indent class="relative flex items-center min-w-0">
        <span data-connector class="hidden absolute left-2 top-1/2 w-4 border-t-2 border-line-2"></span>

        <x-crudPackage::ui.datatable.cell :column="$columns['lead']" :row="$item" />
    </span>

    <span class="hidden lg:flex">
        <x-crudPackage::ui.datatable.cell :column="$columns['badge']" :row="$item" />
    </span>

    <x-crudPackage::ui.datatable.cell :column="$columns['actions']" :row="$item" :row-title="$item->title" record-label="menü öğesini" />
</div>
