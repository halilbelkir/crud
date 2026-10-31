@php
    $card     = 'bg-card border border-line rounded-card px-5 py-6 lg:px-[34px] lg:py-[30px] flex flex-col gap-5';
    $title    = 'font-heading text-[20px] lg:text-[22px]';
    $selected = array_map('strval', old('languages', $value->languages ?? []));
    $image    = fn ($path) => empty($path) ? null : uploadUrl($path);
@endphp

<x-crudPackage::layouts.app
    title="Ayarlar"
    :breadcrumb="[['label' => 'Yönetim'], ['label' => 'Ayarlar']]"
>
    <form method="POST" action="{{ route('settings.update', $value->id) }}" enctype="multipart/form-data" class="flex flex-col gap-3.5">
        @csrf
        @method('PUT')

        <div class="{{ $card }}">
            <div class="{{ $title }}">Genel</div>

            <div class="grid gap-5 lg:grid-cols-2">
                <x-crudPackage::form.input
                    name="title"
                    label="Başlık"
                    :value="old('title', $value->title)"
                    hint="Giriş sayfası ve title meta etiketi etkilenir."
                    required
                />

                <x-crudPackage::form.input
                    name="subtitle"
                    label="Karşılama Mesajı"
                    :value="old('subtitle', $value->subtitle)"
                    required
                />
            </div>

            <x-crudPackage::form.select
                name="languages"
                label="Diller"
                :options="$languages->pluck('title', 'id')->all()"
                :selected="$selected"
                placeholder="Dil seçin"
                :sort="false"
                searchable
                multiple
            />
        </div>

        <div class="{{ $card }}">
            <div class="{{ $title }}">Görünüm</div>

            @if (count($themes) > 1)
                <x-crudPackage::form.segmented
                    name="theme"
                    label="Tema"
                    :options="collect($themes)->mapWithKeys(fn ($theme) => [$theme['name'] => $theme['title']])->all()"
                    :selected="old('theme', $activeTheme)"
                    required
                />
            @else
                <input type="hidden" name="theme" value="{{ $activeTheme }}">
            @endif

            <div class="grid gap-5 lg:grid-cols-2">
                <x-crudPackage::form.color name="color_1" label="Panel Renk 1" :value="old('color_1', $value->color_1)" />
                <x-crudPackage::form.color name="color_2" label="Panel Renk 2" :value="old('color_2', $value->color_2)" />
            </div>

            @if (count($fonts) > 0)
                <x-crudPackage::form.select
                    name="font"
                    label="Font"
                    :options="array_combine($fonts, $fonts)"
                    :selected="old('font', $value->font)"
                    placeholder="Varsayılan font"
                    searchable
                />
            @else
                <x-crudPackage::form.input
                    name="font"
                    label="Font"
                    :value="old('font', $value->font)"
                    placeholder="Google Fonts font adı (ör. Poppins)"
                    hint="Tüm font listesi için .env dosyasına GOOGLE_FONTS_API_KEY ekleyin."
                />
            @endif
        </div>

        <div class="{{ $card }}">
            <div class="{{ $title }}">Görseller</div>

            <div class="grid gap-5 lg:grid-cols-2">
                <x-crudPackage::form.image name="logo" label="Logo" :value="$image($value->logo)" accept="image/jpeg,image/png,image/webp,image/svg+xml" />
                <x-crudPackage::form.image name="icon" label="İkon" :value="$image($value->icon)" accept="image/jpeg,image/png,image/webp,image/svg+xml" />
                <x-crudPackage::form.image
                    name="loader"
                    label="Yükleme Animasyonu"
                    :value="$image($value->loader)"
                    accept="image/gif,image/png,image/webp,image/svg+xml"
                    hint="Boş bırakılırsa varsayılan animasyon kullanılır."
                />
                <x-crudPackage::form.image
                    name="bg_image"
                    label="Arka Plan Resmi"
                    :value="$image($value->bg_image)"
                    hint="Giriş ve şifre sayfalarındaki arka plan görseli."
                />
            </div>
        </div>

        <div class="{{ $card }} !flex-row items-center justify-end">
            <x-crudPackage::ui.button type="submit" size="field" trailing="arrow" class="w-full lg:w-auto lg:px-10">Kaydet</x-crudPackage::ui.button>
        </div>
    </form>
</x-crudPackage::layouts.app>
