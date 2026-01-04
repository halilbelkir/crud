<?php

namespace crudPackage\Models;

use Illuminate\Database\Eloquent\Model;

class MenuItem extends Model
{
    public function crud($route)
    {
        return Crud::whereLike('slug', '%'.$route.'%')->first();
    }

    public function children()
    {
        return $this->hasMany(MenuItem::class, 'parent_id')->orderBy('order');
    }

    public function parent()
    {
        return $this->belongsTo(MenuItem::class, 'parent_id');
    }

    public function scopeWithSpecialItemRolePermissions($query, int $roleGroupId)
    {
        return $query
            ->join('roles', 'roles.menu_item_id', '=', 'menu_items.id')
            ->where('roles.role_group_id', $roleGroupId)
            ->whereAny([
                'roles.browse'
            ], '!=', 0)
            ->select('menu_items.*')
            ->distinct();
    }

    public function roles($roleGroupId)
    {
        return $this->hasMany(Role::class)->where('role_group_id',$roleGroupId)->first();
    }
}
