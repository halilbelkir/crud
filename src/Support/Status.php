<?php

namespace crudPackage\Support;

use Illuminate\Database\Eloquent\Model;

class Status
{
    public static function column(): string
    {
        return config('crudPanel.status.column', 'status');
    }

    public static function models(): array
    {
        return config('crudPanel.status.models', []) + Modules::models();
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
        return static::alias($record) !== null
            && array_key_exists(static::column(), $record->getAttributes());
    }

    public static function allows(Model $record): bool
    {
        return static::supports($record)
            && Permissions::allows(static::permission(static::alias($record)));
    }

    public static function isActive(Model $record): bool
    {
        return (int) $record->{static::column()} === 1;
    }
}
