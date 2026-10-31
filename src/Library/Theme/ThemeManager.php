<?php

namespace crudPackage\Library\Theme;

use Illuminate\Support\Facades\File;

class ThemeManager
{
    public const DEFAULT_THEME = 'default';

    public const PREVIEW_EXTENSIONS = ['png', 'jpg', 'jpeg', 'webp'];

    public static function path(string $theme = ''): string
    {
        return dirname(__DIR__, 3) . '/resources/themes' . ($theme ? '/' . $theme : '');
    }

    public static function previewPath(string $theme): string
    {
        return self::publicPath($theme) . '/previews';
    }

    public static function publicPath(string $theme): string
    {
        return dirname(__DIR__, 3) . '/public/themes/' . $theme;
    }

    public static function asset(string $file): string
    {
        $theme = is_file(self::publicPath(self::active()) . '/' . $file) ? self::active() : self::DEFAULT_THEME;
        $path  = 'crud/themes/' . $theme . '/' . $file;

        return asset($path) . (is_file(public_path($path)) ? '?v=' . filemtime(public_path($path)) : '');
    }

    public static function previews(string $theme): array
    {
        $directory = self::previewPath($theme);

        if (!is_dir($directory))
        {
            return [];
        }

        $files = [];

        foreach (File::files($directory) as $file)
        {
            if (in_array(strtolower($file->getExtension()), self::PREVIEW_EXTENSIONS))
            {
                $files[] = $file->getFilename();
            }
        }

        sort($files, SORT_NATURAL);

        return array_map(fn ($file) => asset('crud/themes/' . $theme . '/previews/' . $file), $files);
    }

    public static function all(): array
    {
        $themes = [];

        foreach (File::directories(self::path()) as $directory)
        {
            $name     = basename($directory);
            $manifest = $directory . '/theme.json';
            $config   = File::exists($manifest) ? json_decode(File::get($manifest), true) : [];

            $themes[$name] =
                [
                    'name'  => $name,
                    'title' => $config['title'] ?? $name,
                    'ready' => (bool) ($config['ready'] ?? false),
                ];
        }

        return $themes;
    }

    public static function ready(): array
    {
        return array_filter(self::all(), fn ($theme) => $theme['ready']);
    }

    public static function selectable(): array
    {
        return array_map(fn ($theme) => $theme + ['previews' => self::previews($theme['name'])], self::ready());
    }

    public static function active(): string
    {
        $theme = settings('theme');

        return array_key_exists($theme, self::ready()) ? $theme : self::DEFAULT_THEME;
    }

    public static function viewPaths(): array
    {
        $paths =
            [
                self::path(self::active()) . '/views',
                self::path(self::DEFAULT_THEME) . '/views',
            ];

        return array_values(array_unique(array_filter($paths, 'is_dir')));
    }
}
