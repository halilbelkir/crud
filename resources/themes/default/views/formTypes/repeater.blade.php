@php
    $listName = isset($language) ? $language->code . '[' . $column->column_name . ']' : $column->column_name;
@endphp

<div data-row-group class="@2xl:col-span-2 flex flex-col gap-3 border-t border-line-soft pt-5">
    <div class="flex items-center justify-between gap-3">
        <span class="label-caps">
            {{ $column->title }}{{ isset($language) ? ' (' . $language->title . ')' : '' }}
            @if ($column->required == 1)
                <span class="text-danger">*</span>
            @endif
        </span>

        <x-crudPackage::ui.button type="button" x-data variant="outline" size="sm" icon="plus" @click="window.addRow($el)">Satır ekle</x-crudPackage::ui.button>
    </div>

    <input type="hidden" name="{{ $listName }}" value="">

    <div data-rows data-sortable data-row-index="{{ $rows ?? 0 }}" x-data x-init="window.initSorting($el)" class="flex flex-col gap-3">
        {!! $elements !!}
    </div>

    @if ($errors->has(crudFieldKey($listName)))
        <span class="text-xs text-danger">{{ $errors->first(crudFieldKey($listName)) }}</span>
    @endif

    <template data-row-template>{!! $template ?? '' !!}</template>
</div>
