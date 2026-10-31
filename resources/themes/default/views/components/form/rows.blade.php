@props([
    'name',
    'label'    => null,
    'addLabel' => 'Satır ekle',
    'items'    => [],
    'fields'   => [],
])

@php
    $marker  = 'data-' . $name . '-row';
    $columns = collect($fields)->map(fn ($field) => $field['width'] ?? 'minmax(0,1fr)')->push('38px')->implode(' ');
@endphp

<div data-row-group class="flex flex-col gap-2.5">
    <div class="flex items-center justify-between gap-3">
        <span class="label-caps">{{ $label }}</span>

        <x-crudPackage::ui.button type="button" x-data variant="outline" size="sm" icon="plus" @click="window.addRow($el)">
            {{ $addLabel }}
        </x-crudPackage::ui.button>
    </div>

    <div data-rows data-row-index="{{ count($items) }}" class="flex flex-col gap-2.5">
        @foreach ($items as $index => $item)
            <div {{ $marker }} class="grid items-start gap-2.5" style="grid-template-columns: {{ $columns }}">
                @foreach ($fields as $field)
                    <x-crudPackage::form.rows-field
                        :group="$name"
                        :index="$index"
                        :field="$field"
                        :value="$item[$field['key']] ?? null"
                    />
                @endforeach

                <button
                    type="button"
                    x-data
                    @click="window.removeRow($el, '[{{ $marker }}]', null)"
                    class="w-[38px] h-[38px] shrink-0 rounded-full border border-line flex items-center justify-center text-muted hover:text-primary hover:border-primary"
                >
                    <x-crudPackage::ui.icon name="trash" :size="15" />
                </button>
            </div>
        @endforeach
    </div>

    <template data-row-template>
        <div {{ $marker }} class="grid items-start gap-2.5" style="grid-template-columns: {{ $columns }}">
            @foreach ($fields as $field)
                <x-crudPackage::form.rows-field
                    :group="$name"
                    index="__index__"
                    :field="$field"
                />
            @endforeach

            <button
                type="button"
                x-data
                @click="window.removeRow($el, '[{{ $marker }}]', null)"
                class="w-[38px] h-[38px] shrink-0 rounded-full border border-line flex items-center justify-center text-muted hover:text-primary hover:border-primary"
            >
                <x-crudPackage::ui.icon name="trash" :size="15" />
            </button>
        </div>
    </template>
</div>
