<?php

namespace crudPackage\Models;

use crudPackage\Notifications\InviteNotification;
use crudPackage\Notifications\ResetPasswordNotification;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Password as PasswordBroker;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'invited_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function sendPasswordResetNotification($token)
    {
        $this->notify(new ResetPasswordNotification($token));
    }

    public function isInvitePending(): bool
    {
        return (bool) $this->force_password_change;
    }

    public function sendInvite(): void
    {
        $token = PasswordBroker::broker('invites')->createToken($this);

        $this->forceFill(['invited_at' => now()])->save();

        $this->notify(new InviteNotification($token));
    }

    public function roleGroup()
    {
        return $this->belongsTo(RoleGroup::class);
    }

    public function hasPermission(string $routeName): bool
    {
        $permissionType = $this->getPermissionTypeFromRoute($routeName);
        $routeExplode   = explode('.',$routeName);
        $route          = $routeExplode[0];
        $response       = false;
        $role           = $this->roleGroup->roles()->forRoute($route)->first();

        if (empty($role))
        {
            $role = $this->roleGroup->roles()->forRoute($routeName)->first();
        }

        if (isset($role) && !empty($role->crud_id) && $role->{$permissionType} == 1)
        {
            $response = true;
        }
        else if (isset($role) && !empty($role->menu_item_id) && $role->browse == 1)
        {
            $response = true;
        }

        return $response;
    }

    private function getPermissionTypeFromRoute(string $routeName): string
    {
        if (str_contains($routeName, 'create') || str_contains($routeName, 'store'))
        {
            return 'add';
        }
        elseif (str_contains($routeName, 'update') || str_contains($routeName, 'edit'))
        {
            return 'edit';
        }
        elseif (str_contains($routeName, 'delete') || str_contains($routeName, 'destroy'))
        {
            return 'delete';
        }
        elseif (str_contains($routeName, 'view') || str_contains($routeName, 'show'))
        {
            return 'read';
        }
        elseif (str_contains($routeName, 'index'))
        {
            return 'browse';
        }

        return 'read';
    }
}
