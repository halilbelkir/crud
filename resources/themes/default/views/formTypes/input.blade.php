@php
    $details     = json_decode($column->detail ?? '{}', true) ?? [];
    $name        = $column->column_name;
    $elementName = isset($column->repeater_name) ? $column->repeater_name . '[' . $name . ']' : (! empty($language) ? $language->code . '[' . $name . ']' : $name);
    $key         = crudFieldKey($elementName);
    $stored      = isset($value) ? ($value->{$name} ?? null) : null;
    $current     = isset($column->repeater_name) ? $stored : old($key, $stored);
    $required    = $column->required == 1 && empty($languageKey);
    $multiple    = ! empty($details['multiple']);
    $upload      = in_array($formType->key, ['image', 'file'], true);
    $slugTarget  = isset($details['slug-generate']['column_name'])
        ? (! empty($language) ? $language->code . '[' . $details['slug-generate']['column_name'] . ']' : $details['slug-generate']['column_name'])
        : null;

    if ($upload && empty($stored) && isset($originalValue) && ! $multiple)
    {
        $stored = $originalValue->{$name} ?? null;
    }

    if ($upload && $multiple && ! empty($languageKey) && $value instanceof \crudPackage\Library\Translation\ModelTranslate)
    {
        $stored = $value->translated($name);
    }

    if (in_array($formType->key, ['date', 'datetime'], true) && ! isset($value) && empty($current))
    {
        $current = $formType->key === 'date' ? now()->format('Y-m-d') : now()->format('Y-m-d\TH:i');
    }

    if ($formType->key === 'datetime' && filled($current))
    {
        try
        {
            $current = \Carbon\Carbon::parse($current)->format('Y-m-d\TH:i');
        }
        catch (\Exception $e)
        {
        }
    }

    $inputType = ['number' => 'number', 'date' => 'date', 'datetime' => 'datetime-local'][$formType->key] ?? 'text';
    $slugInput = $slugTarget
        ? 'const target = $el.form?.querySelector(' . \Illuminate\Support\Js::from('[name="' . $slugTarget . '"]') . '); if (target) target.value = window.slugify($el.value)'
        : null;
    $maxLength = $details['maxlength'] ?? null;

    $files   = [];
    $gallery = $upload && $multiple && isset($value) && isset($originalValue);

    if ($upload && $multiple && filled($stored))
    {
        $decoded = json_decode($stored, true);
        $files   = is_array($decoded) ? $decoded : [$stored];
    }
@endphp

@if ($formType->key === 'hidden')
    <input type="hidden" name="{{ $elementName }}" value="{{ $current }}">
@elseif ($formType->key === 'color')
    <x-crudPackage::form.color :name="$elementName" :value="$current" :required="$required" />
@elseif ($formType->key === 'password')
    <x-crudPackage::form.password
        :name="$elementName"
        :placeholder="$column->title"
        :required="$required && blank($stored)"
        :hint="filled($stored) ? 'Boş bırakırsanız mevcut şifre korunur.' : null"
    />
@elseif ($upload && ! $multiple && $formType->key === 'image')
    <x-crudPackage::form.image :name="$elementName" :value="filled($stored) ? uploadUrl($stored) : null" :removable="$column->required != 1" />
@elseif ($upload)
    <div class="flex flex-col gap-3">
        <x-crudPackage::form.file
            :name="$elementName"
            :image="$formType->key === 'image'"
            :multiple="$multiple"
            :required="$required && $formType->key !== 'image' && empty($stored)"
            :stored="$stored"
            :count="count($files)"
            :refresh="$gallery"
        />

        @if (! $multiple && filled($stored))
            <a href="{{ uploadUrl($stored) }}" target="_blank" class="inline-flex items-center gap-2 text-sm text-primary hover:underline">
                <x-crudPackage::ui.icon name="file" :size="16" />
                {{ basename($stored) }}
            </a>
        @endif

        @if ($gallery)
            @php
                $copyName = ! empty($language) ? $language->code . '[' . $name . '_copy]' : $name . '_copy';
            @endphp

            <div data-refresh="files-{{ $key }}" data-media-group @class(['grid grid-cols-2 @sm:grid-cols-3 @2xl:grid-cols-4 @4xl:grid-cols-6 gap-3', 'hidden' => count($files) === 0])>
                @if (! empty($copy) && count($files) > 0)
                    <input type="hidden" name="{{ $copyName }}[]" value="">
                @endif

                @foreach ($files as $order => $file)
                    @php
                        $preview = filePreview($file, uploadUrl($file));
                    @endphp

                    <div x-data data-media-file="{{ json_encode($preview, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) }}" class="relative rounded-[18px] border border-line-soft bg-card overflow-hidden aspect-square">
                        @if (! empty($copy))
                            <input type="hidden" name="{{ $copyName }}[]" value="{{ $file }}">
                        @endif

                        <button type="button" @click="$dispatch('media-preview', $el)" class="absolute inset-0 flex items-center justify-center">
                            @if ($formType->key === 'image')
                                <img src="{{ $preview['url'] }}" alt="" loading="lazy" class="w-full h-full object-cover">
                            @else
                                <span class="w-full flex flex-col items-center gap-1.5 text-muted px-3">
                                    <x-crudPackage::ui.icon name="file" :size="26" />
                                    <span class="label-caps">{{ $preview['extension'] }}</span>
                                    <x-crudPackage::ui.filename :path="$file" class="max-w-full justify-center text-xs" />
                                </span>
                            @endif
                        </button>

                        @if (empty($copy))
                            <button
                                type="button"
                                x-data
                                @click="$store.dialog.ask({
                                    title: {{ \Illuminate\Support\Js::from(($order + 1) . '. sıradaki dosya silinsin mi?') }},
                                    message: 'Bu işlem geri alınamaz.',
                                    tone: 'danger',
                                    confirmLabel: 'Evet, sil',
                                    action: () => window.deleteRequest({{ \Illuminate\Support\Js::from(route($crud->slug . '.fileDestroy', ['id' => $originalValue->id, 'order' => $order, 'column_name' => $name, 'language_code' => $language->code ?? null, 'language_order' => $languageKey ?? null])) }}),
                                })"
                                class="absolute top-2 right-2 w-8 h-8 rounded-full bg-card border border-line flex items-center justify-center text-muted hover:text-primary"
                            >
                                <x-crudPackage::ui.icon name="trash" :size="15" />
                            </button>
                        @else
                            <button
                                type="button"
                                title="Kopyaya alma"
                                @click="$root.remove()"
                                class="absolute top-2 right-2 w-8 h-8 rounded-full bg-card border border-line flex items-center justify-center text-muted hover:text-primary"
                            >
                                <x-crudPackage::ui.icon name="close" :size="15" />
                            </button>
                        @endif
                    </div>
                @endforeach
            </div>
        @endif
    </div>
@else
    @if ($slugInput)
        <x-crudPackage::form.input
            :name="$elementName"
            :type="$inputType"
            :value="$current"
            :placeholder="$column->title"
            :required="$required"
            :maxlength="$maxLength"
            :size="$size ?? 'md'"
            x-data
            x-on:input="{{ $slugInput }}"
        />
    @else
        <x-crudPackage::form.input
            :name="$elementName"
            :type="$inputType"
            :value="$current"
            :placeholder="$column->title"
            :required="$required"
            :maxlength="$maxLength"
            :size="$size ?? 'md'"
        />
    @endif
@endif
