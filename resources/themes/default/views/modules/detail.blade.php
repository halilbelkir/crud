@php
    $first = $title ?? ($crud->display_single . ' #' . $record->getKey());
    $meta  = [
        $crud->display_single,
        $record->created_at ? $record->created_at->translatedFormat('d.m.Y') . ' tarihinden beri' : null,
    ];
@endphp

<x-crudPackage::layouts.app
    :title="$first"
    :breadcrumb="[['label' => $crud->title, 'url' => route($crud->slug . '.index')], ['label' => $first]]"
>
    <div class="flex flex-col gap-3.5" x-data="{ tab: @js(array_key_first($sections)) }">

        <x-crudPackage::ui.detail-header :title="$first" :meta="$meta">
            <x-slot:actions>
                @if (\crudPackage\Support\Permissions::allows($crud->slug . '.edit'))
                    <x-crudPackage::ui.button
                        x-data
                        @click="$dispatch('form-drawer', { url: {{ \Illuminate\Support\Js::from(route($crud->slug . '.edit', $record->getKey())) }}, title: {{ \Illuminate\Support\Js::from($crud->display_single . ' düzenle') }} })"
                        variant="outline"
                        size="sm"
                        icon="pen"
                    >
                        Düzenle
                    </x-crudPackage::ui.button>
                @endif
            </x-slot:actions>
        </x-crudPackage::ui.detail-header>

        @if (count($sections) > 1)
            <div class="h-11 rounded-[22px] bg-card border border-line p-[3px] flex gap-[3px] self-start max-w-full overflow-x-auto">
                @foreach ($sections as $code => $section)
                    <button
                        type="button"
                        @click="tab = @js($code)"
                        class="px-4 rounded-[19px] text-[13.5px] whitespace-nowrap transition-colors"
                        :class="tab === @js($code) ? 'bg-primary text-white font-medium' : 'text-body'"
                    >{{ $section['title'] }}</button>
                @endforeach
            </div>
        @endif

        @foreach ($sections as $code => $section)
            <div x-show="tab === @js($code)" @if (! $loop->first) x-cloak @endif>
                <x-crudPackage::ui.card :title="$crud->display_single . ' bilgileri'" flush>
                    <div class="px-[22px] py-2">
                        @foreach ($section['rows'] as $row)
                            <x-crudPackage::ui.detail-row :label="$row['label']">
                                @if ($row['inline'])
                                    <span
                                        x-data="inlineField(@js($row['inline']['url']), @js($row['inline']['name']))"
                                        @change="save($event.target)"
                                        class="block w-full max-w-md min-w-0"
                                    >
                                        {!! $row['inline']['html'] !!}
                                    </span>
                                @elseif (count($row['images']) > 0)
                                    <span data-media-group class="flex flex-wrap gap-2">
                                        @foreach ($row['images'] as $image)
                                            <button
                                                type="button"
                                                data-media-file="{{ json_encode(filePreview($image, $image), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) }}"
                                                @click="$dispatch('media-preview', $el)"
                                                class="w-16 h-16 rounded-xl border border-line-soft overflow-hidden bg-subtle"
                                            >
                                                <img src="{{ $image }}" alt="" class="w-full h-full object-cover">
                                            </button>
                                        @endforeach
                                    </span>
                                @elseif ($row['html'] !== null)
                                    <div class="prose-sm max-w-none [&_p]:mb-2">{!! $row['html'] !!}</div>
                                @else
                                    {{ filled($row['value']) ? $row['value'] : '—' }}
                                @endif
                            </x-crudPackage::ui.detail-row>
                        @endforeach
                    </div>
                </x-crudPackage::ui.card>
            </div>
        @endforeach

    </div>
</x-crudPackage::layouts.app>
