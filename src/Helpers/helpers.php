<?php

use crudPackage\Models\Setting;
use Illuminate\Support\Facades\Cache;
use Carbon\Carbon;

function menuLink($menu, ?bool $target = null): array
{
    if (strstr($menu->route,','))
    {
        $arr         = explode(',',$menu->route);
        $menu->route = $arr[0];
        $href        = route($menu->route,$arr[1]);
    }
    else
    {
        $href = $menu->dynamic_route == 1 && \Illuminate\Support\Facades\Route::has($menu->route) ? route($menu->route) : $menu->route;
    }

    return
        [
            'title'  => $menu->title,
            'icon'   => $menu->icon,
            'href'   => $href,
            'route'  => $menu->route,
            'target' => $target ?? $menu->target == 1,
            'active' => request()->routeIs($menu->route),
        ];
}

function crudFieldKey(string $name): string
{
    return trim(str_replace(['[]', '][', '[', ']'], ['', '.', '.', ''], $name), '.');
}

function crudBrand(string $key, mixed $default = null): mixed
{
    return \crudPackage\Support\Brand::get($key, $default);
}

function settings($column)
{
    $cacheName = 'settings';
    $cache     = Cache::remember($cacheName, 480 * 60, function ()
    {
        $settings = Setting::find(1);

        if ($settings)
        {
            return $settings->toArray();
        }

        return null;
    });

    if ($column == 'languages' && !empty($cache))
    {
        return Setting::find(1)?->languageModels() ?? [];
    }

    $columns =
        [
            'logo'     => 'crud/images/logo.svg',
            'loader'   => null,
            'bg_image' => 'crud/images/guest.jpg',
            'icon'     => 'crud/images/fav/android-icon-192x192.png',
            'color_1'  => '#2900FF',
            'color_2'  => '#001244',
            'font'     => null,
            'title'    => 'Zaurac Teknoloji',
            'theme'    => 'default',
        ];

    return $cache[$column] ?? $columns[$column] ?? null;
}

function diffFields(array $old, array $new): array
{
    $diff = [];

    foreach ($new as $key => $value)
    {
        if (!array_key_exists($key, $old) || $key == 'created_at' || $key == 'updated_at')
        {
            continue;
        }

        if ($old[$key] != $value)
        {
            $diff[$key] =
                [
                    'old' => $old[$key],
                    'new' => $value,
                ];
        }
    }

    return $diff;
}

function crudLanguages($crud = null): array
{
    if ($crud && ($crud->translatable ?? 1) == 0)
    {
        return [];
    }

    $languages = settings('languages') ?? [];

    return is_array($languages) ? array_values($languages) : $languages->values()->all();
}

function crudComponent(string $name, array $data = [], $slot = null): string
{
    return view('crudPackage::components.' . $name,
        [
            'attributes' => new \Illuminate\View\ComponentAttributeBag($data),
            'slot'       => new \Illuminate\Support\HtmlString((string) $slot),
        ]
    )->render();
}

function crudStatusBadge($status, ?string $on = null, ?string $off = null, ?string $size = null): string
{
    return $status == 1
        ? crudComponent('badge', ['tone' => 'success', 'size' => $size], $on ?? 'Aktif')
        : crudComponent('badge', ['tone' => 'danger', 'size' => $size], $off ?? 'Pasif');
}

function logAttributeValue($value): string
{
    if (is_array($value) || is_object($value))
    {
        return json_encode($value, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);
    }

    return (string) ($value ?? '');
}

function shortFilename(string $filename, int $limit = 8): string
{
    $ext  = pathinfo($filename, PATHINFO_EXTENSION);
    $name = pathinfo($filename, PATHINFO_FILENAME);

    if (mb_strlen($name) <= $limit)
    {
        return $filename;
    }

    return mb_substr($name, 0, $limit) . '....' . $ext;
}

function getExtension(string $filename): string
{
    return pathinfo($filename, PATHINFO_EXTENSION);
}

function filePreview(string $path, string $url, ?string $download = null): array
{
    $extension = strtolower(getExtension($path));
    $kinds     = [
        'image' => ['jpg', 'jpeg', 'png', 'gif', 'webp', 'svg', 'avif'],
        'pdf'   => ['pdf'],
        'video' => ['mp4', 'webm', 'ogv', 'mov'],
        'audio' => ['mp3', 'wav', 'ogg', 'm4a'],
    ];

    return [
        'name'      => basename($path),
        'url'       => $url,
        'download'  => $download,
        'extension' => $extension ?: 'dosya',
        'kind'      => collect($kinds)->search(fn ($extensions) => in_array($extension, $extensions, true)) ?: 'file',
    ];
}

function trLower(?string $value): string
{
    return mb_strtolower(strtr((string) $value, ['İ' => 'i', 'I' => 'ı']), 'UTF-8');
}

function trUpper(?string $value): string
{
    return mb_strtoupper(strtr((string) $value, ['i' => 'İ', 'ı' => 'I']), 'UTF-8');
}

function trUcfirst(?string $value): string
{
    return trUpper(mb_substr((string) $value, 0, 1, 'UTF-8')) . mb_substr((string) $value, 1, null, 'UTF-8');
}

function uploadUrl($path)
{
    if (empty($path))
    {
        return $path;
    }

    if (preg_match('/^https?:\/\//i', $path))
    {
        return $path;
    }

    $path = preg_replace('/^\/?upload\//', '', $path);

    return \Illuminate\Support\Facades\Storage::disk('upload')->url(ltrim($path, '/'));
}

function googleFonts(): array
{
    $apiKey = env('GOOGLE_FONTS_API_KEY');

    if (empty($apiKey))
    {
        return [];
    }

    return Cache::remember('google_fonts_list', 60 * 60 * 24 * 7, function () use ($apiKey)
    {
        try
        {
            $response = \Illuminate\Support\Facades\Http::timeout(10)->get('https://www.googleapis.com/webfonts/v1/webfonts',
                [
                    'key'  => $apiKey,
                    'sort' => 'alpha',
                ]);

            if ($response->successful())
            {
                return collect($response->json('items'))->pluck('family')->all();
            }
        }
        catch (\Throwable $e)
        {
        }

        return [];
    });
}

function multipleLanguages($status = 0,$locale = null)
{
    if ($status == 1)
    {
        return multipleLanguages()[$locale];
    }
    else if ($status == 2)
    {
        $keys = array_keys(multipleLanguages());

        return array_search($locale, $keys);
    }
    else
    {
        if (count(settings('languages')) > 0)
        {
            $languages = [];

            foreach (settings('languages') as $key => $value)
            {
                $languages[$value->code] = (object) [ 'title' => $value->title,'code' => $value->code];
            }

            return $languages;
        }
    }

    return false;
}