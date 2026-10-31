<?php

namespace crudPackage\Support;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Route;

class Actions
{
    public static function label(string $action): string
    {
        return config('crudPanel.actions.' . $action . '.label', $action);
    }

    public static function icon(string $action): string
    {
        return config('crudPanel.actions.' . $action . '.icon', 'file');
    }

    public static function menu(string $resource, Model $record, array $options = []): array
    {
        $items = [];

        if (static::allowed($resource, 'show'))
        {
            $items[] = [
                'label' => static::label('show'),
                'icon'  => static::icon('show'),
                'href'  => static::url($resource, 'show', $record),
            ];
        }

        if (static::allowed($resource, 'edit'))
        {
            $items[] = [
                'label'  => static::label('edit'),
                'icon'   => static::icon('edit'),
                'href'   => static::url($resource, 'edit', $record),
                'drawer' => $options['drawer'] ?? true,
                'title'  => $options['editTitle'] ?? static::label('edit'),
            ];
        }

        $items = array_merge($items, $options['extra'] ?? []);

        if (static::allowed($resource, 'delete') && ! in_array('delete', $options['except'] ?? [], true))
        {
            if ($items)
            {
                $items[] = 'separator';
            }

            $items[] = [
                'label'   => static::label('delete'),
                'icon'    => static::icon('delete'),
                'href'    => static::url($resource, 'delete', $record),
                'method'  => 'DELETE',
                'danger'  => true,
                'confirm' => $options['deleteConfirm'] ?? true,
            ];
        }

        return $items;
    }

    protected static function allowed(string $resource, string $action): bool
    {
        $route = static::route($resource, $action);

        return Route::has($route) && Permissions::allows($route);
    }

    protected static function route(string $resource, string $action): string
    {
        return $resource . '.' . config('crudPanel.actions.' . $action . '.route');
    }

    protected static function url(string $resource, string $action, Model $record): string
    {
        return route(static::route($resource, $action), $record);
    }
}
