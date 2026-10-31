<?php

namespace crudPackage\Support;

use crudPackage\Models\MenuItem;

class Menu
{
    public static function groups(): array
    {
        return once(fn () => static::build());
    }

    protected static function build(): array
    {
        $groups = [];
        $items  = [static::dashboard()];

        foreach (static::records(0) as $record)
        {
            $children = static::children($record);

            if ($children === [])
            {
                $items[] = static::item($record);

                continue;
            }

            $groups[] = static::group(null, $items);
            $groups[] = static::group($record->title, $children);
            $items    = [];
        }

        $groups[] = static::group(null, $items);
        $groups[] = static::group('Yönetim', array_map(fn ($record) => static::item($record), static::records(1)));

        return array_values(array_filter($groups, fn (array $group) => $group['items'] !== []));
    }

    public static function collapsedByDefault(): array
    {
        return [];
    }

    public static function primary(int $limit = 4): array
    {
        $items = [];

        foreach (static::groups() as $group)
        {
            foreach ($group['items'] as $item)
            {
                $items[] = $item;
            }
        }

        return array_slice($items, 0, $limit);
    }

    public static function allowed(array $item): bool
    {
        return Permissions::allows($item['route']);
    }

    public static function isActive(array $item): bool
    {
        return $item['active'];
    }

    public static function icon(?string $icon): string
    {
        return static::resolveIcon($icon) ?? 'grid';
    }

    public static function resolveIcon(?string $icon): ?string
    {
        if (! is_string($icon) || $icon === '')
        {
            return null;
        }

        if (in_array($icon, static::icons(), true))
        {
            return $icon;
        }

        foreach (config('crudPanel.menu_icon_aliases', []) as $legacy => $name)
        {
            if (str_contains($icon, $legacy))
            {
                return $name;
            }
        }

        return null;
    }

    public static function icons(): array
    {
        return config('crudPanel.menu_icons', []);
    }

    protected static function records(int $main): array
    {
        return MenuItem::where('menu_id', 1)
            ->where('main', $main)
            ->where('parent_id', 0)
            ->orderBy('order')
            ->get()
            ->all();
    }

    protected static function children(MenuItem $record): array
    {
        $children = [];

        foreach ($record->children as $child)
        {
            $children[] = static::item($child, $record->target == 1);
        }

        return $children;
    }

    protected static function item(MenuItem $record, ?bool $target = null): array
    {
        $link = menuLink($record, $target);

        return
            [
                'label'  => $link['title'],
                'href'   => $link['href'],
                'route'  => $link['route'],
                'icon'   => static::icon($link['icon']),
                'target' => $link['target'],
                'active' => $link['active'] || request()->routeIs(explode('.', $link['route'])[0] . '.*'),
            ];
    }

    protected static function dashboard(): array
    {
        return
            [
                'label'  => 'Dashboard',
                'href'   => route('dashboard'),
                'route'  => 'dashboard',
                'icon'   => 'grid',
                'target' => false,
                'active' => request()->routeIs('dashboard*'),
            ];
    }

    protected static function group(?string $name, array $items): array
    {
        $items = array_values(array_filter($items, fn (array $item) => $item['route'] === 'dashboard' || static::allowed($item)));

        return
            [
                'group'     => $name,
                'items'     => $items,
                'collapsed' => false,
                'active'    => (bool) array_filter($items, fn (array $item) => $item['active']),
            ];
    }
}
