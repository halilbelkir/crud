<?php

namespace crudPackage\Support;

use Illuminate\Database\Eloquent\Model;

class Sortable
{
    public static function column(?string $alias = null): ?string
    {
        $ordering = Modules::ordering();

        if ($alias !== null && array_key_exists($alias, $ordering))
        {
            return $ordering[$alias]['column'];
        }

        return config('crudPanel.sortable.column', 'order');
    }

    public static function direction(?string $alias = null): string
    {
        return Modules::ordering()[$alias]['direction'] ?? 'asc';
    }

    public static function models(): array
    {
        return config('crudPanel.sortable.models', []) + Modules::models();
    }

    public static function alias(Model $record): ?string
    {
        $map = array_flip(static::models());

        return $map[$record::class] ?? null;
    }

    public static function modelFor(string $alias): ?string
    {
        return static::models()[$alias] ?? null;
    }

    public static function permission(string $alias): string
    {
        return $alias . '.edit';
    }

    public static function supports(Model $record): bool
    {
        $alias  = static::alias($record);
        $column = $alias !== null ? static::column($alias) : null;

        return $column !== null && array_key_exists($column, $record->getAttributes());
    }
}
