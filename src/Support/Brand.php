<?php

namespace crudPackage\Support;

use Illuminate\Support\Facades\Storage;

class Brand
{
    public const FONT_URL = 'https://fonts.bunny.net/css?family=braah-one:400|saira:300,400,500,600,700|poppins:400,500,600,700&display=swap';

    public static function get(string $key, mixed $default = null): mixed
    {
        return data_get(once(fn () => static::values()), $key, $default);
    }

    public static function lightLogo(): ?string
    {
        $path = static::get('logo.path');

        if (! $path || ! is_file($path))
        {
            return null;
        }

        $content = file_get_contents($path);
        $image   = function_exists('imagecreatefromstring') ? @imagecreatefromstring($content) : false;

        if (! $image)
        {
            return $content;
        }

        imagepalettetotruecolor($image);

        if (((imagecolorat($image, 0, 0) >> 24) & 0x7F) !== 127)
        {
            return $content;
        }

        imagealphablending($image, false);
        imagesavealpha($image, true);
        imagefilter($image, IMG_FILTER_BRIGHTNESS, 255);

        ob_start();
        imagepng($image);

        return ob_get_clean();
    }

    protected static function values(): array
    {
        $logo   = static::asset(settings('logo'));
        $icon   = static::asset(settings('icon'));
        $loader = static::asset(settings('loader'));
        $font   = settings('font');
        $family = $font ? "'" . $font . "', sans-serif" : null;

        return
            [
                'name'        => settings('title'),
                'tagline'     => settings('subtitle'),
                'description' => settings('subtitle'),
                'privacy_url' => null,
                'logo'        =>
                    [
                        'horizontal'       => $logo,
                        'horizontal_light' => $logo,
                        'mark'             => $icon,
                        'favicon'          => $icon,
                        'icon'             => $icon,
                        'touch'            => $icon,
                        'share'            => $logo,
                        'path'             => static::path(settings('logo')),
                    ],
                'font'        =>
                    [
                        'url'     => static::FONT_URL,
                        'custom'  => static::fontUrl($font),
                        'heading' => $family ?? "'Braah One', sans-serif",
                        'body'    => $family ?? "'Saira', sans-serif",
                        'label'   => $family ?? "'Poppins', sans-serif",
                    ],
                'color'       =>
                    [
                        'primary' => settings('color_1') ?: '#A8002E',
                        'accent'  => settings('color_2') ?: '#A88D62',
                        'ink'     => '#1C1A1B',
                        'muted'   => '#8A8380',
                        'line'    => '#EDE6DA',
                        'subtle'  => '#FBF9F5',
                        'app'     => '#F7F4EF',
                    ],
                'auth'        =>
                    [
                        'image'    => static::asset(settings('bg_image')),
                        'headline' => e(settings('title')),
                        'website'  => null,
                        'phone'    => null,
                    ],
                'loader'      =>
                    [
                        'default'     => $loader,
                        'standart'    => $loader,
                        'small'       => $loader,
                        'tiny'        => $loader,
                        'button'      => $loader,
                        'light'       => $loader,
                        'light_small' => $loader,
                    ],
            ];
    }

    protected static function asset(?string $path): ?string
    {
        if (empty($path))
        {
            return null;
        }

        return str_contains($path, 'crud') ? asset($path) : uploadUrl($path);
    }

    protected static function path(?string $path): ?string
    {
        if (empty($path) || preg_match('/^https?:\/\//i', $path))
        {
            return null;
        }

        if (str_contains($path, 'crud'))
        {
            return public_path($path);
        }

        return Storage::disk('upload')->path(preg_replace('/^\/?upload\//', '', $path));
    }

    protected static function fontUrl(?string $font): ?string
    {
        if (empty($font))
        {
            return null;
        }

        return 'https://fonts.googleapis.com/css2?family=' . str_replace(' ', '+', $font) . ':wght@300;400;500;600;700&display=swap';
    }
}
