@php
    $prefix = 'repeaterArea[' . $index . ']';
@endphp

<div data-repeater-row data-row="{{ $index }}" class="grid items-start gap-2.5 rounded-[20px] border border-line-soft bg-subtle p-3.5" style="grid-template-columns: 18px minmax(0, 1fr) 38px">
    <span data-drag class="flex items-center h-[38px] shrink-0 text-disabled cursor-grab active:cursor-grabbing touch-none">
        <x-crudPackage::ui.icon name="grip" :size="18" />
    </span>

    <div class="grid sm:grid-cols-[1.4fr_1fr] gap-3.5">
        <x-crudPackage::form.json
            :name="$prefix . '[area_info]'"
            label="Alan bilgileri"
            :value="$value['area_info'] ?? ''"
            :placeholder="$sample"
            required
        />

        <x-crudPackage::form.select
            :name="$prefix . '[form_type_id]'"
            label="Tip"
            :options="$formTypes"
            :selected="$value['form_type_id'] ?? null"
            placeholder="Tip seçin"
            searchable
            required
        />
    </div>

    <button
        type="button"
        x-data
        @click="window.removeRow($el, '[data-repeater-row]', null)"
        class="w-[38px] h-[38px] shrink-0 rounded-full border border-line bg-card flex items-center justify-center text-muted hover:text-primary hover:border-primary"
    >
        <x-crudPackage::ui.icon name="trash" :size="15" />
    </button>
</div>
