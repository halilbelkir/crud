@php
    $storage   = \Illuminate\Support\Facades\Storage::disk($disk);
    $canUpload = \crudPackage\Support\Permissions::allows('media.upload');
    $canDelete = \crudPackage\Support\Permissions::allows('media.delete');
    $trail     = [['label' => 'Medya', 'url' => route('media.index')]];

    foreach ($segments as $index => $segment)
    {
        $trail[] = ['label' => $segment, 'url' => route('media.index', ['path' => implode('/', array_slice($segments, 0, $index + 1))])];
    }

    $parent = count($segments) > 0 ? implode('/', array_slice($segments, 0, -1)) : null;
@endphp

<x-crudPackage::layouts.app
    title="Medya"
    :breadcrumb="array_merge([['label' => 'Yönetim']], $trail)"
>
    <div class="flex flex-col gap-3.5" x-data>

        <div class="bg-card border border-line rounded-card px-5 py-4 lg:px-6 flex flex-col lg:flex-row lg:items-center gap-3">
            <div class="flex items-center gap-2 min-w-0 flex-1 flex-wrap text-sm">
                @foreach ($trail as $index => $crumb)
                    @if ($index > 0)
                        <x-crudPackage::ui.icon name="chevron-right" :size="14" class="text-disabled shrink-0" />
                    @endif

                    <a href="{{ $crumb['url'] }}" @class(['truncate hover:text-primary', 'font-medium text-ink' => $loop->last, 'text-muted' => ! $loop->last])>{{ $crumb['label'] }}</a>
                @endforeach

                <span class="text-faint">· {{ count($folders) }} klasör, {{ count($files) }} dosya</span>
            </div>

            @if ($canUpload)
                <div class="flex items-center gap-2.5">
                    <x-crudPackage::ui.button x-data variant="outline" size="sm" icon="plus" @click="$dispatch('modal-open', 'media-folder')">Klasör</x-crudPackage::ui.button>
                    <x-crudPackage::ui.button x-data size="sm" icon="upload" @click="$dispatch('modal-open', 'media-upload')">Dosya yükle</x-crudPackage::ui.button>
                </div>
            @endif
        </div>

        <div data-refresh="media" class="bg-card border border-line rounded-card p-3.5 lg:p-5">
            @if (count($folders) === 0 && count($files) === 0 && $parent === null)
                <x-crudPackage::ui.empty-state icon="inbox" title="Klasör boş" message="Henüz dosya yüklenmemiş. Dosya yükleyerek veya klasör oluşturarak başlayın." />
            @else
                <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 xl:grid-cols-6 gap-3">
                    @if ($parent !== null)
                        <a
                            href="{{ route('media.index', ['path' => $parent ?: null]) }}"
                            class="rounded-[20px] border border-line-soft bg-subtle hover:border-line-3 flex flex-col items-center justify-center gap-2 aspect-square text-muted"
                        >
                            <x-crudPackage::ui.icon name="chevron-left" :size="26" />
                            <span class="text-sm">Üst klasör</span>
                        </a>
                    @endif

                    @foreach ($folders as $folder)
                        <div class="relative rounded-[20px] border border-line-soft bg-subtle hover:border-line-3 aspect-square">
                            <a href="{{ route('media.index', ['path' => $folder]) }}" class="absolute inset-0 flex flex-col items-center justify-center gap-2 px-3 text-center">
                                <span class="w-14 h-14 rounded-2xl bg-tint-accent-2 text-on-tint-accent flex items-center justify-center">
                                    <x-crudPackage::ui.icon name="inbox" :size="24" />
                                </span>
                                <span class="text-sm font-medium truncate max-w-full">{{ basename($folder) }}</span>
                            </a>

                            @if ($canDelete)
                                <x-crudPackage::ui.dropdown class="!absolute top-2 right-2" width="w-[200px]">
                                    <x-slot:trigger>
                                        <button type="button" class="w-8 h-8 rounded-full bg-card border border-line flex items-center justify-center text-muted hover:text-ink">
                                            <x-crudPackage::ui.icon name="dots" :size="16" />
                                        </button>
                                    </x-slot:trigger>

                                    <x-crudPackage::ui.dropdown-item
                                        :href="route('media.delete', ['path' => $folder])"
                                        method="DELETE"
                                        icon="trash"
                                        danger
                                        :confirm="['title' => basename($folder) . ' klasörü silinsin mi?', 'message' => 'Klasördeki tüm dosyalar da kalıcı olarak silinir.', 'verify' => 'SİL', 'confirmLabel' => 'Kalıcı olarak sil']"
                                    >Sil</x-crudPackage::ui.dropdown-item>
                                </x-crudPackage::ui.dropdown>
                            @endif
                        </div>
                    @endforeach

                    @foreach ($files as $file)
                        @php
                            $url     = url($storage->url($file));
                            $preview = filePreview($file, $url, route('media.download', ['path' => $file]));
                        @endphp

                        <div data-media-file="{{ json_encode($preview, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) }}" class="relative rounded-[20px] border border-line-soft bg-subtle hover:border-line-3 aspect-square flex flex-col">
                            <button type="button" @click="$dispatch('media-preview', $el)" class="flex-1 min-h-0 flex items-center justify-center bg-card rounded-t-[19px] overflow-hidden">
                                @if ($preview['kind'] === 'image')
                                    <img src="{{ $url }}" alt="" loading="lazy" class="w-full h-full object-contain">
                                @else
                                    <span class="flex flex-col items-center gap-1.5 text-muted">
                                        <x-crudPackage::ui.icon name="file" :size="28" />
                                        <span class="label-caps">{{ $preview['extension'] }}</span>
                                    </span>
                                @endif
                            </button>

                            <x-crudPackage::ui.filename :path="$file" class="px-3 py-2 border-t border-line-soft text-xs" />

                            <x-crudPackage::ui.dropdown class="!absolute top-2 right-2" width="w-[220px]">
                                <x-slot:trigger>
                                    <button type="button" class="w-8 h-8 rounded-full bg-card border border-line flex items-center justify-center text-muted hover:text-ink">
                                        <x-crudPackage::ui.icon name="dots" :size="16" />
                                    </button>
                                </x-slot:trigger>

                                <x-crudPackage::ui.dropdown-item icon="eye" @click="open = false; $dispatch('media-preview', $el)">Görüntüle</x-crudPackage::ui.dropdown-item>
                                <x-crudPackage::ui.dropdown-item :copy="$url" icon="copy" copied="Bağlantı kopyalandı">Bağlantıyı kopyala</x-crudPackage::ui.dropdown-item>
                                <x-crudPackage::ui.dropdown-item :href="route('media.download', ['path' => $file])" icon="export">İndir</x-crudPackage::ui.dropdown-item>

                                @if ($canDelete)
                                    <x-crudPackage::ui.dropdown-separator />

                                    <x-crudPackage::ui.dropdown-item
                                        :href="route('media.delete', ['path' => $file])"
                                        method="DELETE"
                                        icon="trash"
                                        danger
                                        :confirm="['title' => basename($file) . ' silinsin mi?', 'message' => 'Dosyayı kullanan sayfalarda görsel görünmez olur.', 'confirmLabel' => 'Evet, sil']"
                                    >Sil</x-crudPackage::ui.dropdown-item>
                                @endif
                            </x-crudPackage::ui.dropdown>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>

    </div>

    @if ($canUpload)
        <x-crudPackage::ui.modal name="media-upload" title="Dosya yükle" max-width="max-w-[620px]">
            <form method="POST" action="{{ route('media.upload') }}" enctype="multipart/form-data" class="flex flex-col gap-3.5">
                @csrf
                <input type="hidden" name="path" value="{{ $path }}">

                <x-crudPackage::ui.dropzone name="files" icon="upload" title="Dosyaları buraya sürükleyin" accept="*/*" multiple />

                @error('files')
                    <span class="text-xs text-primary">{{ $message }}</span>
                @enderror

                <div class="flex items-center gap-2.5 pt-1">
                    <x-crudPackage::ui.button type="button" x-data variant="outline" size="field" class="flex-1" @click="$dispatch('modal-close', 'media-upload')">Vazgeç</x-crudPackage::ui.button>
                    <x-crudPackage::ui.button type="submit" size="field" class="flex-[1.2]" trailing="arrow">Yükle</x-crudPackage::ui.button>
                </div>
            </form>
        </x-crudPackage::ui.modal>

        <x-crudPackage::ui.modal name="media-folder" title="Yeni klasör" max-width="max-w-[480px]">
            <form method="POST" action="{{ route('media.createFolder') }}" class="flex flex-col gap-3.5">
                @csrf
                <input type="hidden" name="path" value="{{ $path }}">

                <x-crudPackage::form.input name="name" label="Klasör adı" :value="old('name')" required autofocus />

                <div class="flex items-center gap-2.5 pt-1">
                    <x-crudPackage::ui.button type="button" x-data variant="outline" size="field" class="flex-1" @click="$dispatch('modal-close', 'media-folder')">Vazgeç</x-crudPackage::ui.button>
                    <x-crudPackage::ui.button type="submit" size="field" class="flex-[1.2]" trailing="arrow">Oluştur</x-crudPackage::ui.button>
                </div>
            </form>
        </x-crudPackage::ui.modal>

        @if ($errors->has('name'))
            <div x-data x-init="$nextTick(() => $dispatch('modal-open', 'media-folder'))"></div>
        @elseif ($errors->has('files') || $errors->has('files.*'))
            <div x-data x-init="$nextTick(() => $dispatch('modal-open', 'media-upload'))"></div>
        @endif
    @endif
</x-crudPackage::layouts.app>
