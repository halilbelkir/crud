<?php

namespace crudPackage\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use crudPackage\Notifications\ResetPasswordNotification;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function sendPasswordResetNotification($token)
    {
        $this->notify(new ResetPasswordNotification($token));
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
        $cacheName      = Auth::id() . '_' .implode('_', $routeExplode);

        $role = $this->roleGroup->roles()
            ->whereHas('crud', fn($query) => $query->whereLike('slug', '%' . $route . '%'))
            ->first();

        return $role ? $role->{$permissionType} == 1 : false;
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
