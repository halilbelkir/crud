@php
    $languages = crudLanguages($crud);
    $editing   = isset($value) && empty($copying);
    $action    = $editing ? route($crud->slug . '.update', $value->id) : route($crud->slug . '.store');
    $back      = $crud->only_edit == 1 ? null : route($crud->slug . '.index');
    $drawer    = request()->ajax();
    $body      = $drawer ? 'flex flex-col gap-5' : 'bg-card border border-line rounded-card px-5 py-6 lg:px-[34px] lg:py-[30px] flex flex-col gap-5';
    $footer    = $drawer
        ? '-mx-5 lg:-mx-8 mt-1 px-5 lg:px-8 pt-4 lg:pt-5 border-t border-line-soft flex items-center gap-3'
        : 'bg-card border border-line rounded-card px-5 py-4 lg:px-[34px] flex items-center justify-end gap-3';
@endphp

<form
    method="POST"
    action="{{ $action }}"
    enctype="multipart/form-data"
    class="@container flex flex-col {{ $drawer ? 'gap-5' : 'gap-3.5' }}"
    x-data="{ tab: @js($languages[0]->code ?? null) }"
    @field-invalid="tab = $event.target.closest('[data-language-tab]')?.dataset.languageTab ?? tab"
>
    @csrf
    @if ($editing)
        @method('PUT')
    @endif

    @if (! empty($copying))
        <input type="hidden" name="crud_copy_id" value="{{ $value->id }}">
    @endif

    @if ($crud->content)
        <x-crudPackage::ui.alert tone="warning" icon="info">{!! $crud->content !!}</x-crudPackage::ui.alert>
    @endif

    @if ($errors->any())
        <x-crudPackage::ui.alert tone="danger" icon="alert">Formda düzeltilmesi gereken alanlar var.</x-crudPackage::ui.alert>
    @endif

    <div class="{{ $body }}">
        @if (count($languages) > 0)
            <div class="h-11 rounded-[22px] border border-line p-[3px] flex gap-[3px] self-start max-w-full overflow-x-auto">
                @foreach ($languages as $language)
                    @php $failed = collect($errors->keys())->contains(fn ($key) => str_starts_with($key, $language->code . '.')); @endphp

                    <button
                        type="button"
                        @click="tab = @js($language->code)"
                        class="px-4 rounded-[19px] flex items-center gap-2 text-[13.5px] whitespace-nowrap transition-colors"
                        :class="tab === @js($language->code) ? 'bg-primary text-white font-medium' : 'text-body'"
                    >
                        {{ $language->title }}

                        @if ($failed)
                            <span class="w-2 h-2 rounded-full bg-primary" :class="tab === @js($language->code) && 'bg-white'"></span>
                        @endif
                    </button>
                @endforeach
            </div>

            @foreach ($languages as $language)
                <div x-show="tab === @js($language->code)" @if (! $loop->first) x-cloak @endif data-language-tab="{{ $language->code }}" class="grid @2xl:grid-cols-2 gap-5">
                    {!! $elementTabs[$language->code] ?? '' !!}
                </div>
            @endforeach
        @else
            <div class="grid @2xl:grid-cols-2 gap-5">
                {!! $elements !!}
            </div>
        @endif
    </div>

    <div class="{{ $footer }}">
        @if ($back || $drawer)
            <x-crudPackage::ui.cancel-button :href="$back" size="field" />
        @endif

        <x-crudPackage::ui.button type="submit" size="field" trailing="arrow" class="flex-1 {{ $drawer ? '' : 'lg:flex-none lg:px-10' }}">Kaydet</x-crudPackage::ui.button>
    </div>
</form>
