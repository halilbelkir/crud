@props([
    'name'     => null,
    'image'    => false,
    'multiple' => false,
    'required' => false,
    'stored'   => null,
    'count'    => 0,
    'refresh'  => false,
])

@php
    $key    = $name ? crudFieldKey($name) : null;
    $error  = $key ? ($errors->first($key) ?: $errors->first($key . '.*')) : null;
    $noun   = $image ? 'görsel' : 'dosya';
    $action = $multiple ? ($image ? 'Görselleri seç' : 'Dosyaları seç') : ($image ? 'Görsel seç' : 'Dosya seç');
    $empty  = match (true)
    {
        $multiple && $count > 0 => 'Seçilenler mevcut ' . ($image ? 'görsellere' : 'dosyalara') . ' eklenir',
        ! $multiple && filled($stored) => 'Yüklü ' . $noun . ' korunur',
        default => ucfirst($noun) . ' seçilmedi',
    };
@endphp

<div
    data-file-picker
    x-data="{
        files: [],
        pick(input)
        {
            this.files.forEach((file) => file.url && URL.revokeObjectURL(file.url));
            this.files = Array.from(input.files).map((file) => ({ name: file.name, url: file.type.startsWith('image/') ? URL.createObjectURL(file) : null }));
        },
        clear()
        {
            this.$refs.input.value = '';
            this.pick(this.$refs.input);
        },
    }"
    class="flex flex-col gap-3 rounded-[20px] border px-4 py-3 {{ $error ? 'bg-tint-danger border-danger' : 'bg-subtle border-line-2' }}"
>
    <div class="flex items-center gap-4">
        <span class="relative w-14 h-14 shrink-0 rounded-2xl bg-card border border-line-soft flex items-center justify-center overflow-hidden">
            <template x-if="files[0]?.url">
                <img :src="files[0].url" alt="" class="w-full h-full object-cover">
            </template>

            <template x-if="! files[0]?.url">
                <x-crudPackage::ui.icon :name="$image ? 'upload' : 'file'" :size="20" class="text-disabled" />
            </template>

            <span
                x-show="files.length > 1"
                x-cloak
                x-text="files.length"
                class="absolute bottom-1 right-1 min-w-5 h-5 px-1 rounded-full bg-primary text-white text-[11px] font-semibold flex items-center justify-center"
            ></span>
        </span>

        <div class="min-w-0 flex-1 flex flex-col items-start gap-1.5">
            <label class="inline-flex items-center gap-2 h-8 rounded-2xl border border-line-2 bg-card px-3.5 font-label text-[12.5px] font-semibold text-ink cursor-pointer hover:border-line-3 focus-within:border-primary">
                <x-crudPackage::ui.icon name="upload" :size="14" />
                {{ $action }}

                <input
                    x-ref="input"
                    type="file"
                    @if ($name) name="{{ $multiple ? $name . '[]' : $name }}" id="{{ $name }}" @endif
                    @if ($multiple) multiple @endif
                    @if ($image) accept="image/*" @endif
                    @if ($required) required @endif
                    class="sr-only"
                    @change="pick($el)"
                >
            </label>

            <span
                @if ($refresh && $key) data-refresh="picker-{{ $key }}" @endif
                class="max-w-full text-xs text-muted truncate"
                x-text="files.length === 0 ? {{ \Illuminate\Support\Js::from($empty) }} : (files.length === 1 ? files[0].name : files.length + ' {{ $noun }} seçildi')"
            >{{ $empty }}</span>
        </div>

        <button
            type="button"
            x-show="files.length"
            x-cloak
            @click="clear()"
            class="shrink-0 text-xs text-muted hover:text-primary"
        >
            Temizle
        </button>
    </div>

    @if ($multiple)
        <div x-show="files.length > 1" x-cloak class="flex flex-wrap gap-2">
            <template x-for="file in files" :key="file.name">
                <span :title="file.name" class="w-12 h-12 rounded-xl bg-card border border-line-soft flex items-center justify-center overflow-hidden">
                    <template x-if="file.url">
                        <img :src="file.url" alt="" class="w-full h-full object-cover">
                    </template>

                    <template x-if="! file.url">
                        <x-crudPackage::ui.icon name="file" :size="18" class="text-disabled" />
                    </template>
                </span>
            </template>
        </div>
    @endif

    @if ($error)
        <span class="text-xs text-danger">{{ $error }}</span>
    @endif
</div>
