<?php

namespace crudPackage\Support;

class Form
{
    public static function dotted(string $name): string
    {
        return rtrim(str_replace(['[', ']'], ['.', ''], $name), '.');
    }

    public static function old(?string $name, mixed $default = null): mixed
    {
        if (! $name)
        {
            return $default;
        }

        $value = old(static::dotted($name));

        if ($value === null || is_array($value))
        {
            return $default;
        }

        return $value;
    }

    public static function error(?string $name): ?string
    {
        if (! $name)
        {
            return null;
        }

        return session('errors')?->first(static::dotted($name)) ?: null;
    }
}
