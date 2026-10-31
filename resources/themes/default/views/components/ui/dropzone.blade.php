@props([
    'name'     => 'file',
    'icon'     => 'qr',
    'title'    => 'Görseli buraya sürükleyin',
    'action'   => 'Dosya seç',
    'accept'   => 'image/*',
    'multiple' => false,
])

<label
    x-data="{ over: false, names: [] }"
    @dragover.prevent="over = true"
    @dragleave.prevent="over = false"
    @drop.prevent="over = false; $refs.input.files = $event.dataTransfer.files; $refs.input.dispatchEvent(new Event('change', { bubbles: true }))"
    {{ $attributes->class([
        'flex flex-col items-center gap-3 rounded-[25px] border-2 border-dashed px-5 py-[30px] text-center cursor-pointer transition-colors',
        'bg-subtle',
    ]) }}
    :class="over ? 'border-primary' : 'border-line-3'"
>
    <input
        x-ref="input"
        type="file"
        name="{{ $name }}{{ $multiple ? '[]' : '' }}"
        accept="{{ $accept }}"
        @if ($multiple) multiple @endif
        class="sr-only"
        @change="names = Array.from($event.target.files).map(file => file.name)"
    >

    <span class="relative w-14 h-14 shrink-0">
        <span class="absolute inset-0 rounded-full border-[3px] border-[color:var(--chart-soft)]"></span>
        <span class="absolute inset-2 rounded-full bg-tint-primary text-primary flex items-center justify-center">
            <x-crudPackage::ui.icon :name="$icon" :size="20" />
        </span>
    </span>

    <span x-show="! names.length" class="text-sm font-medium">{{ $title }}</span>

    <span x-show="names.length" x-cloak class="text-sm font-medium text-primary" x-text="names.join(', ')"></span>

    <span x-show="! names.length" class="text-xs text-faint">veya</span>

    <span
        x-show="! names.length"
        class="inline-flex items-center h-[38px] rounded-[19px] border border-primary px-5 font-label text-[11px] font-semibold tracking-[0.1em] uppercase text-primary"
    >
        {{ $action }}
    </span>
</label>
