@props([
    'compact' => false,
])

<x-crudPackage::ui.dropdown width="w-[290px]" :class="$compact ? 'flex-none' : ''">
    <x-slot:trigger>
        @if ($compact)
            <button type="button" class="w-ctl h-ctl rounded-[20px] border border-line-2 flex items-center justify-center text-body">
                <x-crudPackage::ui.icon name="export" :size="16" />
            </button>
        @else
            <button type="button" class="h-ctl rounded-[20px] border border-line-2 flex items-center gap-2.5 px-4 text-sm text-body hover:text-ink">
                <x-crudPackage::ui.icon name="export" :size="15" />
                Dışa aktar
            </button>
        @endif
    </x-slot:trigger>

    <span class="px-3 pt-1.5 pb-2 label-caps-sm">Biçim</span>

    <x-crudPackage::ui.dropdown-item :href="request()->fullUrlWithQuery(['export' => 'xlsx'])" class="!h-[50px]" data-download>
        <span class="w-[30px] h-[30px] rounded-[9px] bg-tint-success text-on-tint-success flex items-center justify-center font-label text-[11px] font-semibold">XLS</span>
        <span class="flex-1 min-w-0">
            <span class="block text-sm text-ink">Excel (.xlsx)</span>
            <span class="block text-[11.5px] text-muted">Biçimli tablo · önerilen</span>
        </span>
    </x-crudPackage::ui.dropdown-item>

    <x-crudPackage::ui.dropdown-item :href="request()->fullUrlWithQuery(['export' => 'csv'])" class="!h-[50px]" data-download>
        <span class="w-[30px] h-[30px] rounded-[9px] bg-neutral text-body flex items-center justify-center font-label text-[11px] font-semibold">CSV</span>
        <span class="flex-1 min-w-0">
            <span class="block text-sm text-ink">CSV dosyası</span>
            <span class="block text-[11.5px] text-muted">Ham veri · içe aktarma</span>
        </span>
    </x-crudPackage::ui.dropdown-item>

    <x-crudPackage::ui.dropdown-item :href="request()->fullUrlWithQuery(['export' => 'pdf'])" class="!h-[50px]" data-download>
        <span class="w-[30px] h-[30px] rounded-[9px] bg-tint-primary text-primary flex items-center justify-center font-label text-[11px] font-semibold">PDF</span>
        <span class="flex-1 min-w-0">
            <span class="block text-sm text-ink">PDF çıktısı</span>
            <span class="block text-[11.5px] text-muted">Yazdırılabilir liste</span>
        </span>
    </x-crudPackage::ui.dropdown-item>
</x-crudPackage::ui.dropdown>
