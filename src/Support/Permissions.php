<?php

namespace crudPackage\Support;

class Permissions
{
    public static function allows(string $routeName): bool
    {
        $user = auth()->user();

        if (! $user)
        {
            return false;
        }

        return $user->roleGroup?->id == 1 || $user->hasPermission($routeName);
    }

    public static function authorize(string $routeName): void
    {
        abort_unless(static::allows($routeName), 403);
    }
}
