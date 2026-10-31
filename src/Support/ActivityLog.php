<?php

namespace crudPackage\Support;

use crudPackage\Models\Crud;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;
use Spatie\Activitylog\Models\Activity;

class ActivityLog
{
    public static function events(): array
    {
        return config('crudPanel.logs.events', []);
    }

    public static function label(?string $event): string
    {
        return config('crudPanel.logs.events.' . $event . '.label', $event ?? '—');
    }

    public static function tone(?string $event): string
    {
        return config('crudPanel.logs.events.' . $event . '.tone', 'neutral');
    }

    public static function updated(?string $event): bool
    {
        return $event === config('crudPanel.logs.updated');
    }

    public static function field(?string $table, string $key): string
    {
        $labels = once(fn () => Crud::query()
            ->with('items:id,crud_id,title,column_name')
            ->get(['id', 'table_name'])
            ->mapWithKeys(fn (Crud $crud) => [static::normalize($crud->table_name) => $crud->items->pluck('title', 'column_name')->all()])
            ->all());

        $label = $labels[static::normalize((string) $table)][$key] ?? null;

        if (filled($label) && $label !== $key)
        {
            return $label;
        }

        return config('crudPanel.logs.fields.' . $key, Str::ucfirst(str_replace('_', ' ', $key)));
    }

    public static function table(?string $name): string
    {
        if ($name === null)
        {
            return '—';
        }

        $titles = once(fn () => Crud::query()
            ->get(['title', 'table_name'])
            ->mapWithKeys(fn (Crud $crud) => [static::normalize($crud->table_name) => $crud->title])
            ->all());

        return $titles[static::normalize($name)] ?? config('crudPanel.logs.tables.' . $name, Str::headline($name));
    }

    public static function tables($query = null): array
    {
        return ($query ?? Activity::query())
            ->distinct()
            ->orderBy('log_name')
            ->pluck('log_name')
            ->filter()
            ->mapWithKeys(fn ($name) => [$name => static::table($name)])
            ->all();
    }

    public static function subject(Activity $activity): string
    {
        $properties = $activity->properties?->toArray() ?? [];
        $values     = static::after($properties) ?? static::before($properties) ?? [];

        foreach (['title', 'name', 'name_surname', 'label', 'email', 'key'] as $field)
        {
            if (filled($values[$field] ?? null) && is_scalar($values[$field]))
            {
                return (string) $values[$field];
            }
        }

        return static::table($activity->log_name) . ' #' . ($activity->subject_id ?? '—');
    }

    public static function before(array $properties): ?array
    {
        return $properties['old'] ?? null;
    }

    public static function after(array $properties): ?array
    {
        return $properties['new'] ?? $properties['attributes'] ?? null;
    }

    public static function isHidden(string $key): bool
    {
        return Str::is(config('crudPanel.logs.hidden', []), $key);
    }

    public static function attributes(?array $values): array
    {
        $clean = [];

        foreach ((array) $values as $key => $value)
        {
            if (in_array($key, config('crudPanel.logs.skipped', []), true))
            {
                continue;
            }

            $clean[$key] = static::isHidden($key) ? '••••••••' : static::plain($value);
        }

        return $clean;
    }

    public static function changes(?array $old, ?array $new): array
    {
        $before = static::attributes($old);
        $after  = static::attributes($new);
        $diff   = [];

        foreach ($after as $key => $value)
        {
            if (! array_key_exists($key, $before) || $before[$key] === $value)
            {
                continue;
            }

            $diff[$key] = [
                'old' => $before[$key],
                'new' => $value,
            ];
        }

        return $diff;
    }

    protected static function normalize(string $table): string
    {
        return str_replace('-', '_', $table);
    }

    protected static function plain($value): string
    {
        if (is_array($value) || is_object($value))
        {
            return json_encode($value, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);
        }

        if (is_string($value) && preg_match('/^\d{4}-\d{2}-\d{2}[ T]\d{2}:\d{2}:\d{2}/', $value))
        {
            return Carbon::parse($value)->format('d.m.Y H:i:s');
        }

        if (is_bool($value))
        {
            return $value ? '1' : '0';
        }

        return (string) ($value ?? '—');
    }
}
