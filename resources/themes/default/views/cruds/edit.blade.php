@php
    $card    = 'bg-card border border-line rounded-card px-5 py-6 lg:px-[34px] lg:py-[30px] flex flex-col gap-5';
    $heading = 'font-heading text-[20px] lg:text-[22px]';
    $editing = $crud->exists;
    $hasOld  = session()->hasOldInput('columns');
    $action  = $editing ? route('cruds.update', $crud) : route('cruds.store');
    $back    = $editing ? route('cruds.show', $crud) : route('cruds.index');
    $sample  = json_encode(
    [
        'column_name' => 'alan_adi',
        'title'       => 'Form element başlığı',
        'validation'  => 'required',
        'class'       => 'col-md-6',
        'option'      => ['key' => 'value'],
        'rows'        => 4,
    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
@endphp

<x-crudPackage::layouts.app
    :title="$editing ? $crud->title . ' düzenle' : $crud->title . ' modülünü oluştur'"
    :breadcrumb="[['label' => 'Modüller', 'url' => route('cruds.index')], ['label' => $editing ? $crud->title : 'Yeni modül']]"
>
    <form method="POST" action="{{ $action }}" class="flex flex-col gap-3.5">
        @csrf
        @if ($editing)
            @method('PUT')
        @endif

        <input type="hidden" name="table_name" value="{{ $crud->table_name }}">

        @if ($errors->any() && ! $errors->hasAny(['relationship_title', 'relationship', 'relationship_table_name', 'relationship_table_model', 'show_column', 'match_column', 'relationship_column_name', 'relationship_pivot_table_name', 'repeater_column_name', 'repeaterArea']))
            <x-crudPackage::ui.alert tone="danger" icon="alert">Formda düzeltilmesi gereken alanlar var.</x-crudPackage::ui.alert>
        @endif

        <div class="{{ $card }}">
            <div class="flex items-center justify-between gap-3">
                <div class="{{ $heading }}">Modül bilgisi</div>
                <x-crudPackage::ui.badge tone="neutral">{{ $crud->table_name }}</x-crudPackage::ui.badge>
            </div>

            <div class="grid gap-5 lg:grid-cols-2">
                <x-crudPackage::form.input
                    name="title"
                    label="Menü başlık"
                    :value="old('title', $crud->title)"
                    x-data
                    x-on:input="{{ $editing ? '' : 'const target = $el.form?.querySelector(\'[name=slug]\'); if (target) target.value = window.slugify($el.value).toLowerCase()' }}"
                    required
                />

                <x-crudPackage::form.input name="slug" label="Link" :value="old('slug', $crud->slug)" hint="Panelde modülün açılacağı adres." required />

                <x-crudPackage::form.input name="display_single" label="Tekli sayfa başlık" :value="old('display_single', $crud->display_single)" required />

                <x-crudPackage::form.input name="display_plural" label="Çoklu sayfa başlık" :value="old('display_plural', $crud->display_plural)" required />

                <x-crudPackage::form.input name="model" label="Model dizini" :value="old('model', $crud->model)" hint="App\Models altında bir Eloquent modeli olmalı." required />

                <x-crudPackage::form.select
                    name="icon"
                    label="İkon"
                    :options="$icons"
                    :selected="old('icon', \crudPackage\Support\Menu::resolveIcon($crud->icon))"
                    placeholder="Varsayılan ikon"
                    searchable
                    icons
                />

                <x-crudPackage::form.select
                    name="order_column_name"
                    label="Sıralama alanı"
                    :options="$columns"
                    :selected="old('order_column_name', $area['order_column_name'] ?? null)"
                    placeholder="Varsayılan sıralama"
                    searchable
                />

                <x-crudPackage::form.select
                    name="order_direction"
                    label="Sıralama yönü"
                    :options="['asc' => 'Artan (A → Z)', 'desc' => 'Azalan (Z → A)']"
                    :selected="old('order_direction', $area['order_direction'] ?? null)"
                    placeholder="Artan"
                    :sort="false"
                />
            </div>

            <div class="grid gap-5 lg:grid-cols-2">
                <x-crudPackage::form.textarea
                    name="content"
                    label="Açıklama"
                    :value="old('content', $crud->content)"
                    :rows="5"
                    hint="Modülün form sayfasının üstünde bilgi notu olarak görünür."
                />

                <x-crudPackage::form.json
                    name="other_route"
                    label="Diğer route"
                    :value="old('other_route', $crud->other_route ?? '{}')"
                    :sample="asset('crud/json/crud_other_route.json')"
                    hint="Kayıt sonrası yönlendirilecek route bilgileri (JSON, isteğe bağlı)."
                />
            </div>

            <div class="grid gap-3 sm:grid-cols-2">
                <x-crudPackage::form.toggle
                    name="only_edit"
                    label="Sadece düzenleme adımı olsun"
                    :checked="(bool) old('only_edit', $crud->only_edit == 1)"
                    hint="Liste sayfası olmaz; tek kayıt düzenlenir."
                />

                <x-crudPackage::form.toggle
                    name="translatable"
                    label="Çok dilli yapı aktif"
                    :checked="(bool) old('translatable', ($crud->translatable ?? 1) == 1)"
                />
            </div>
        </div>

        <div class="{{ $card }}">
            <div class="flex flex-wrap items-center justify-between gap-3">
                <div class="flex flex-col gap-1">
                    <div class="{{ $heading }}">Modül alanları</div>
                    <span class="text-[13px] text-muted">
                        Sürükleyerek sıralayın; yeni sıra kaydettiğinizde uygulanır.
                    </span>
                </div>

                @if ($editing)
                    <div class="flex flex-wrap items-center gap-2">
                        <x-crudPackage::ui.button type="button" x-data variant="outline" size="sm" icon="link" @click="$dispatch('modal-open', 'crud-relationship')">İlişki ekle</x-crudPackage::ui.button>
                        <x-crudPackage::ui.button type="button" x-data variant="outline" size="sm" icon="plus" @click="$dispatch('modal-open', 'crud-repeater')">Tekrarlanan alan ekle</x-crudPackage::ui.button>
                    </div>
                @endif
            </div>

            @error('columns')
                <x-crudPackage::ui.alert tone="danger" icon="alert">{{ $message }}</x-crudPackage::ui.alert>
            @enderror

            <div data-refresh="crud-fields" x-data x-init="window.initSorting($el)" class="flex flex-col gap-3">
                @foreach ($fields as $index => $field)
                    @php
                        $item     = $field['item'];
                        $prefix   = 'columns[' . $index . ']';
                        $special  = $item && ($item->relationship == 1 || $item->repeater == 1);
                        $lockedTo = $locked[$field['name']] ?? null;
                        $typeId   = $special ? $item->form_type_id : ($lockedTo ? ($formTypeKeys[$lockedTo] ?? null) : old('columns.' . $index . '.form_type_id', $item->form_type_id ?? null));
                        $fixed    = $special || $lockedTo;
                    @endphp

                    <div data-row="{{ $field['name'] }}" class="rounded-[20px] border border-line-soft bg-subtle p-4 lg:p-5 flex flex-col gap-4">
                        <div class="flex flex-wrap items-start justify-between gap-3">
                            <div class="flex items-start gap-3 min-w-0">
                                <span data-drag class="flex items-center pt-0.5 shrink-0 text-disabled cursor-grab active:cursor-grabbing touch-none">
                                    <x-crudPackage::ui.icon name="grip" :size="18" />
                                </span>

                                <div class="flex flex-col gap-1 min-w-0">
                                    <div class="flex flex-wrap items-center gap-2">
                                        <span class="font-medium text-ink break-all">{{ $field['name'] }}</span>

                                        @if ($item && $item->relationship == 1)
                                            <x-crudPackage::ui.badge tone="primary" size="sm">İlişkili</x-crudPackage::ui.badge>
                                        @elseif ($item && $item->repeater == 1)
                                            <x-crudPackage::ui.badge tone="primary" size="sm">Tekrarlanan alan</x-crudPackage::ui.badge>
                                        @endif
                                    </div>

                                    <span class="text-[13px] text-muted">{{ $field['type'] }} · {{ $field['nullable'] ? 'Boş olabilir' : 'Zorunlu sütun' }}</span>
                                </div>
                            </div>

                            @if ($special)
                                @php
                                    $removeUrl   = $item->relationship == 1 ? route('cruds.relationship.destroy', $item->id) : route('cruds.repeater.destroy', $item->id);
                                    $removeTitle = $item->relationship == 1 ? $item->title . ' ilişkisi kaldırılsın mı?' : $item->title . ' tekrarlanan alanı kaldırılsın mı?';
                                @endphp

                                <x-crudPackage::ui.button
                                    type="button"
                                    x-data
                                    variant="outline"
                                    size="sm"
                                    icon="trash"
                                    @click="$store.dialog.ask({
                                        title: {{ \Illuminate\Support\Js::from($removeTitle) }},
                                        message: 'Alan normal alana döner; tanımlı ayrıntılar silinir.',
                                        tone: 'danger',
                                        confirmLabel: 'Evet, kaldır',
                                        action: () => window.deleteRequest({{ \Illuminate\Support\Js::from($removeUrl) }}),
                                    })"
                                >
                                    Kaldır
                                </x-crudPackage::ui.button>
                            @endif
                        </div>

                        <input type="hidden" name="{{ $prefix }}[column_name]" value="{{ $field['name'] }}">
                        <input type="hidden" name="{{ $prefix }}[id]" value="{{ $item->id ?? '' }}">

                        <div class="grid gap-4 lg:grid-cols-2">
                            <x-crudPackage::form.input
                                :name="$prefix . '[title]'"
                                label="Görünecek isim"
                                :value="old('columns.' . $index . '.title', $item->title ?? $field['name'])"
                            />

                            @if ($fixed)
                                <input type="hidden" name="{{ $prefix }}[form_type_id]" value="{{ $typeId }}">

                                <x-crudPackage::form.input label="Tip" :value="$formTypes[$typeId] ?? '—'" disabled />
                            @else
                                <x-crudPackage::form.select
                                    :name="$prefix . '[form_type_id]'"
                                    label="Tip"
                                    :options="$formTypes"
                                    :selected="$typeId"
                                    placeholder="Tip seçin"
                                    searchable
                                    required
                                />
                            @endif
                        </div>

                        <x-crudPackage::form.field label="Görünürlük">
                            <div class="flex flex-wrap gap-x-6 gap-y-3">
                                @foreach ($visibilities as $key => $label)
                                    <x-crudPackage::form.checkbox
                                        :name="$prefix . '[' . $key . ']'"
                                        :label="$label"
                                        size="sm"
                                        :checked="$hasOld ? (bool) old('columns.' . $index . '.' . $key) : (($item->{$key} ?? 0) == 1)"
                                    />
                                @endforeach
                            </div>
                        </x-crudPackage::form.field>

                        <x-crudPackage::form.json
                            :name="$prefix . '[detail]'"
                            label="İsteğe bağlı ayrıntılar"
                            :value="old('columns.' . $index . '.detail', $item->detail ?? '{}')"
                            hint="JSON biçiminde (ör. seçenekler, slug üretimi, çoklu seçim)."
                        />
                    </div>
                @endforeach
            </div>
        </div>

        <div class="{{ $card }} !flex-row items-center justify-end gap-3">
            <x-crudPackage::ui.cancel-button :href="$back" size="field" />
            <x-crudPackage::ui.button type="submit" size="field" trailing="arrow" class="flex-1 lg:flex-none lg:px-10">Kaydet</x-crudPackage::ui.button>
        </div>
    </form>

    @if ($editing)
        <x-crudPackage::ui.modal name="crud-relationship" title="İlişki ekle" max-width="max-w-[720px]">
            <form
                method="POST"
                action="{{ route('cruds.relationship.store', $crud) }}"
                class="flex flex-col gap-4"
                x-data="relationshipForm({
                    url: @js(route('cruds.create.columns')),
                    relation: @js(old('relationship', '')),
                    table: @js(old('relationship_table_name')),
                    picked: @js(['show_column' => old('show_column'), 'match_column' => old('match_column')]),
                })"
                @change="pick($event)"
            >
                @csrf
                @method('PUT')

                <x-crudPackage::form.input name="relationship_title" label="Görünecek isim" :value="old('relationship_title')" required />

                <div class="grid gap-4 sm:grid-cols-2">
                    <x-crudPackage::form.select
                        name="relationship"
                        label="İlişki"
                        :options="['hasOne' => 'Has One', 'hasMany' => 'Has Many', 'belongsTo' => 'Belongs To', 'belongsToMany' => 'Belongs To Many']"
                        :selected="old('relationship')"
                        :sort="false"
                        required
                    />

                    <div x-show="relation === 'belongsToMany'" x-cloak>
                        <x-crudPackage::form.select name="relationship_pivot_table_name" label="Pivot tablo" :options="$tables" :selected="old('relationship_pivot_table_name')" searchable required />
                    </div>

                    <div x-show="relation !== 'belongsToMany'">
                        <x-crudPackage::form.select name="relationship_column_name" label="Referans alınacak alan" :options="$columns" :selected="old('relationship_column_name')" searchable required />
                    </div>

                    <x-crudPackage::form.select name="relationship_table_name" label="Referans tablo" :options="$tables" :selected="old('relationship_table_name')" searchable required />

                    <x-crudPackage::form.input name="relationship_table_model" label="Referans tablo model" :value="old('relationship_table_model')" placeholder="App\Models\Category" required />

                    <x-crudPackage::form.select name="show_column" label="Görüntülenecek alan" :options="[]" placeholder="Önce tablo seçin" searchable required />

                    <x-crudPackage::form.select name="match_column" label="Eşleşecek alan" :options="[]" placeholder="Önce tablo seçin" searchable required />
                </div>

                <div class="flex items-center gap-2.5 pt-1">
                    <x-crudPackage::ui.button type="button" x-data variant="outline" size="field" class="flex-1" @click="$dispatch('modal-close', 'crud-relationship')">Vazgeç</x-crudPackage::ui.button>
                    <x-crudPackage::ui.button type="submit" size="field" class="flex-[1.2]" trailing="arrow">Kaydet</x-crudPackage::ui.button>
                </div>
            </form>
        </x-crudPackage::ui.modal>

        @php
            $areas = old('repeaterArea', [['area_info' => '', 'form_type_id' => null]]);
            $area  = fn ($index, $value = []) => view('crudPackage::cruds.repeater-area', ['index' => $index, 'value' => $value, 'formTypes' => $formTypes, 'sample' => $sample])->render();
        @endphp

        <x-crudPackage::ui.modal name="crud-repeater" title="Tekrarlanan alan ekle" max-width="max-w-[720px]">
            <form method="POST" action="{{ route('cruds.repeater.store', $crud) }}" class="flex flex-col gap-4" data-row-group>
                @csrf
                @method('PUT')

                <x-crudPackage::form.select name="repeater_column_name" label="Referans alınacak alan" :options="$columns" :selected="old('repeater_column_name')" searchable required />

                <div class="flex items-center justify-between gap-3">
                    <span class="label-caps">Tekrarlanan alanlar</span>
                    <x-crudPackage::ui.button type="button" x-data variant="outline" size="sm" icon="plus" @click="window.addRow($el)">Satır ekle</x-crudPackage::ui.button>
                </div>

                @error('repeaterArea')
                    <span class="text-xs text-primary">{{ $message }}</span>
                @enderror

                <div data-rows data-sortable data-row-index="{{ count($areas) }}" x-data x-init="window.initSorting($el)" class="flex flex-col gap-3">
                    @foreach ($areas as $index => $value)
                        {!! $area($index, $value) !!}
                    @endforeach
                </div>

                <template data-row-template>{!! $area('__index__') !!}</template>

                <div class="flex items-center gap-2.5 pt-1">
                    <x-crudPackage::ui.button type="button" x-data variant="outline" size="field" class="flex-1" @click="$dispatch('modal-close', 'crud-repeater')">Vazgeç</x-crudPackage::ui.button>
                    <x-crudPackage::ui.button type="submit" size="field" class="flex-[1.2]" trailing="arrow">Kaydet</x-crudPackage::ui.button>
                </div>
            </form>
        </x-crudPackage::ui.modal>

        @if ($errors->hasAny(['relationship_title', 'relationship', 'relationship_table_name', 'relationship_table_model', 'show_column', 'match_column', 'relationship_column_name', 'relationship_pivot_table_name']))
            <div x-data x-init="$nextTick(() => $dispatch('modal-open', 'crud-relationship'))"></div>
        @elseif ($errors->has('repeater_column_name') || collect($errors->keys())->contains(fn ($key) => str_starts_with($key, 'repeaterArea')))
            <div x-data x-init="$nextTick(() => $dispatch('modal-open', 'crud-repeater'))"></div>
        @endif
    @endif
</x-crudPackage::layouts.app>
