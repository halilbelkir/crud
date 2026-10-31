@props([
    'name'        => null,
    'label'       => null,
    'size'        => 'md',
    'required'    => false,
    'hint'        => null,
    'placeholder' => null,
    'strength'    => false,
])

@php
    $error = $name ? $errors->first(crudFieldKey($name)) : null;
@endphp

<div x-data="{ visible: false, value: '' }">
    <x-crudPackage::form.field :label="$label" :name="$name" :required="$required" :hint="$hint">
        <x-crudPackage::form.box :size="$size" :error="$error">
            <input
                :type="visible ? 'text' : 'password'"
                x-model="value"
                @if ($name) name="{{ $name }}" id="{{ $name }}" @endif
                placeholder="{{ $placeholder ?? $label }}"
                @if ($required) required @endif
                {{ $attributes->class('flex-1 min-w-0 bg-transparent text-[15px] text-ink placeholder:text-faint outline-none') }}
            >

            <button type="button" @click="visible = ! visible" class="shrink-0 transition-colors" :class="visible ? 'text-primary' : 'text-disabled hover:text-muted'">
                <x-crudPackage::ui.icon name="eye" :size="17" />
            </button>
        </x-crudPackage::form.box>
    </x-crudPackage::form.field>

    @if ($strength)
        @php
            $rules = [
                'hasLength' => 'En az 8 karakter',
                'hasDigit'  => 'Rakam',
                'hasSymbol' => 'Özel karakter',
            ];
        @endphp

        <div
            x-data="{
                get hasLength() { return this.value.length >= 8 },
                get hasDigit() { return /[0-9]/.test(this.value) },
                get hasSymbol() { return /[^A-Za-z0-9]/.test(this.value) },
                get hasUpper() { return /[A-Z]/.test(this.value) },
                get score() { return [this.hasLength, this.hasDigit, this.hasSymbol, this.hasUpper].filter(Boolean).length },
                get scoreLabel() { return ['Çok zayıf', 'Zayıf', 'Orta güçlü', 'Güçlü', 'Çok güçlü'][this.score] },
            }"
            class="mt-4 flex flex-col gap-3"
        >
            <div class="flex items-center gap-4">
                <div class="flex gap-1.5">
                    <template x-for="i in 4" :key="i">
                        <span
                            class="w-11 h-1.5 rounded-[3px]"
                            :class="i <= score ? (score >= 4 ? 'bg-success' : (score >= 3 ? 'bg-warning' : 'bg-danger')) : 'bg-line'"
                        ></span>
                    </template>
                </div>

                <span class="text-sm font-medium text-muted-2" x-text="scoreLabel" x-show="value.length > 0"></span>
            </div>

            <div class="flex flex-wrap items-center gap-x-5 gap-y-2 text-[13px]">
                @foreach ($rules as $rule => $text)
                    <span class="flex items-center gap-1.5" :class="{{ $rule }} ? 'text-on-tint-success' : 'text-muted'">
                        <template x-if="{{ $rule }}">
                            <x-crudPackage::ui.icon name="check" :size="14" :stroke="2.6" class="shrink-0" />
                        </template>

                        <template x-if="! {{ $rule }}">
                            <span class="w-3.5 h-3.5 rounded-full border-2 border-line-3 shrink-0"></span>
                        </template>

                        {{ $text }}
                    </span>
                @endforeach
            </div>
        </div>
    @endif
</div>
