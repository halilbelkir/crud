<?php

namespace crudPackage\Support;

use crudPackage\Models\MailTemplate;

class MailTemplates
{
    public const FIELDS = ['subject', 'heading', 'content', 'button', 'note'];

    public static function all(): array
    {
        return config('crudPanel.mail_templates', []);
    }

    public static function exists(string $key): bool
    {
        return array_key_exists($key, static::all());
    }

    public static function definition(string $key): array
    {
        return static::all()[$key] ?? [];
    }

    public static function stored(string $key): ?MailTemplate
    {
        return MailTemplate::where('key', $key)->first();
    }

    public static function fields(string $key): array
    {
        $definition = static::definition($key);
        $stored     = static::stored($key);

        return collect(static::FIELDS)
            ->mapWithKeys(fn ($field) => [$field => filled($stored?->{$field}) ? $stored->{$field} : ($definition[$field] ?? null)])
            ->all();
    }

    public static function render(string $key, array $values): array
    {
        $fields = static::fields($key);
        $values = ['site' => crudBrand('name')] + $values;
        $plain  = static::tokens($values);
        $html   = static::tokens(array_map(fn ($value) => e((string) $value), $values));

        return
            [
                'subject' => strtr((string) $fields['subject'], $plain),
                'heading' => strtr((string) $fields['heading'], $plain),
                'content' => strtr((string) $fields['content'], $html),
                'button'  => strtr((string) $fields['button'], $plain),
                'note'    => strtr((string) $fields['note'], $html),
                'preview' => strtr((string) (static::definition($key)['preview'] ?? ''), $plain),
            ];
    }

    public static function samples(string $key): array
    {
        return collect(static::definition($key)['variables'] ?? [])
            ->filter(fn ($variable) => $variable['sample'] !== null)
            ->map(fn ($variable) => $variable['sample'])
            ->all();
    }

    protected static function tokens(array $values): array
    {
        return collect($values)
            ->mapWithKeys(fn ($value, $name) => ['{' . $name . '}' => (string) $value])
            ->all();
    }
}
